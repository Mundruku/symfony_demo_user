<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Model\UserDto;
use App\Controller\Traits\ApiResponseTrait;

#[Route('users')]
final class UserController extends AbstractController
{

    use ApiResponseTrait;
    /**
     * POST /users
     * Create a new user.
     */
    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] UserDto $userDto,
        EntityManagerInterface $em, 
        ValidatorInterface $validator
    ): JsonResponse {

        $user = new User();
        $user->setName($userDto->name);
        $user->setEmail($userDto->email);
        
        $user->setStatus($userDto->status);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->errorResponse((string) $errors, 400);
        }

        $em->persist($user);
        $em->flush();

        return $this->successResponse($user, 201);
    }

    /**
     * GET /users
     * List all users, newest first, optional status filter.
     */
    #[Route('', methods: ['GET'])]
    public function list(Request $request, UserRepository $repository): JsonResponse
    {
        $statusParam = $request->query->get('status');
      
        $criteria = [];

        if ($statusParam) {
            $enumStatus = UserStatus::tryFrom($statusParam);
            if ($enumStatus) {
                $criteria['status'] = $enumStatus;
            }
            else {
                return $this->errorResponse('Invalid status filter', 400);
            }
        }

        $users = $repository->findBy($criteria, ['created_at' => 'DESC']);

        return $this->successResponse($users);
    }

    /**
     * GET /users/analytics
     * Numeric counts for user growth.
     */
    #[Route('/analytics', methods: ['GET'])]
    public function analytics(UserRepository $repository): JsonResponse
    {
        $totalUsers = $repository->count([]);

        // Dates for filtering
        $fifteenDaysAgo = new \DateTimeImmutable('-15 days');
        $sevenDaysAgo = new \DateTimeImmutable('-7 days');

        // Users created in last 15 days
        $last15DaysCount = $repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.created_at >= :date')
            ->setParameter('date', $fifteenDaysAgo)
            ->getQuery()
            ->getSingleScalarResult();

        // Users created in last 7 days (to calculate average)
        $last7DaysCount = $repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.created_at >= :date')
            ->setParameter('date', $sevenDaysAgo)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'total_users' => (int) $totalUsers,
            'created_last_15_days' => (int) $last15DaysCount,
            'average_new_users_per_day_last_7_days' => round($last7DaysCount / 7, 2),
        ]);
    }
}