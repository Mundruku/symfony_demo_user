<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class RedisCacheService
{
    private TagAwareAdapter $cache;
    private LoggerInterface $logger;
    private array $config;

    public function __construct(
        string $redisUrl,
        LoggerInterface $logger,
        array $cacheConfig = []
    ) {
        $this->logger = $logger;
        $this->config = array_merge([
            'default_ttl' => 3600,
            'namespace' => 'app',
            'compression' => true,
        ], $cacheConfig);

        // Create Redis connection
        $client = RedisAdapter::createConnection($redisUrl);
        
        // Configure Redis client
        if ($this->config['compression']) {
            $client->setOption(\Redis::OPT_COMPRESSION, \Redis::COMPRESSION_LZF);
        }
        
        // Create cache adapter with tagging support
        $redisAdapter = new RedisAdapter(
            $client,
            $this->config['namespace'],
            $this->config['default_ttl']
        );
        
        $this->cache = new TagAwareAdapter($redisAdapter);
    }

    /**
     * Get item from cache or compute if not exists (Cache-Aside pattern)
     */
    public function get(string $key, callable $callback, array $tags = [], ?int $ttl = null): mixed
    {
        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($callback, $tags, $ttl) {
                // Set TTL
                if ($ttl !== null) {
                    $item->expiresAfter($ttl);
                }
                
                // Add tags for invalidation
                if (!empty($tags)) {
                    $item->tag($tags);
                }
                
                // Execute callback to get fresh data
                $value = $callback();
                
                // Log cache miss
                $this->logger->info('Cache miss', [
                    'key' => $item->getKey(),
                    'tags' => $tags
                ]);
                
                return $value;
            });
        } catch (\Exception $e) {
            // Fallback to callback on cache failure
            $this->logger->error('Cache get error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return $callback();
        }
    }

    /**
     * Delete item from cache
     */
    public function delete(string $key): bool
    {
        try {
            $result = $this->cache->delete($key);
            $this->logger->info('Cache deleted', ['key' => $key]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache delete error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Invalidate all items with given tag
     */
    public function invalidateTags(array $tags): bool
    {
        try {
            $result = $this->cache->invalidateTags($tags);
            $this->logger->info('Cache tags invalidated', ['tags' => $tags]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache invalidation error', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clear entire cache (use with caution)
     */
    public function clear(): bool
    {
        try {
            return $this->cache->clear();
        } catch (\Exception $e) {
            $this->logger->error('Cache clear error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'adapter' => get_class($this->cache->getPool()),
            'namespace' => $this->config['namespace'],
            'compression' => $this->config['compression']
        ];
    }
}