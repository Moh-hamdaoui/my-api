<?php 
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface; // Utilisation de l'interface

class ApiAuthController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager; 

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager 
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']); 
        $user->setPassword($hashedPassword);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'User created successfully!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
    $data = json_decode($request->getContent(), true);
    $email = $data['email'];
    $password = $data['password'];

    $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

    if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
        return new JsonResponse(['error' => 'Invalid credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    $token = $this->jwtManager->create($user);

    return new JsonResponse(['token' => $token], JsonResponse::HTTP_OK);
    }

}
