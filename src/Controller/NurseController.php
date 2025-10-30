<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/nurse')]
final class NurseController extends AbstractController
{

    // Route to call the findByName method to search for a nurse by name
    #[Route('/name/{name}', name: 'app_nurse_find_by_name')]

    /**
     * Summary of findByName
     * @param string $name
     * @param \App\Repository\UserRepository $userRepository
     * @return JsonResponse
     */
    public function findByName(string $name, UserRepository $userRepository): JsonResponse
    {
        // Busca una enfermera por nombre usando el repositorio
        $nurse = $userRepository->findOneByName($name);

        if ($nurse) {
            return $this->json([
                'name' => $nurse->getName(),
                'user' => $nurse->getUser(),
                'password' => $nurse->getPassword(),
            ], 200);
        } else {
            // Si no se encuentra, devuelve un error 404
            return $this->json(['error' => 'Nurse not found'], 404);
        }
    }


    #[Route('/index', name: 'index')]
    public function getAll(UserRepository $userRepository): JsonResponse
    {
        // Obtenemos todos los usuarios (en tu caso, enfermeras)
        $nurses = $userRepository->findAll();

        // Mapeamos los objetos a arrays para devolver en formato JSON
        $data = array_map(function ($nurse) {
            return [
                'id' => $nurse->getId(),
                'user' => $nurse->getUser(),
                'password' => $nurse->getPassword(),
            ];
        }, $nurses);

        return $this->json($data, 200);
    }

    //Codigo Javier


    // #[Route('/login', methods: ['POST'])]
    // public function login(Request $request): JsonResponse
    // {
    //     //$nurses = json_decode(file_get_contents(__DIR__ . '/../../public/nurses.json'), true);
    //     $data = json_decode($request->getContent(), true);
    //     $username = $data['username'] ?? '';
    //     $pwd = $data['password'] ?? '';
    //     $nurse = $nurseRepository->findOneBy(['username'=>$username]);
    //     if($nurse)
    //     return this->json(
    //         [
    //             'message'=>'Credenciales inválidas'
    //         ]);
    // }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['user'] ?? '';
        $password = $data['password'] ?? '';

        $nurse = $userRepository->findOneBy(['user' => $username]);

        if ($nurse && $nurse->getPassword() === $password) {
            return $this->json(['message' => 'Login successful'], 200);
        }

        return $this->json(['error' => 'Invalid credentials'], 401);
    }


    //Codigo de Olalla (Dejamos el de javier)


    // {
    //     #[Route(path: '/nurse/login', methods: ['POST'])]

    //     public function login(Request $request): JsonResponse
    //     {
    //         $nurses = json_decode(json: file_get_contents(__DIR__ . '/../../public/nurses.json'), associative: true);
    //         $data = json_decode($request->getContent(), true);

    //         foreach ($nurses as $nurse) {
    //             if ($nurse['username'] === ($data['username'] ?? '') &&
    //                 $nurse['password'] === ($data['password'] ?? '')) {
    //                 return $this->json(true);
    //             }
    //         }
    //         return $this->json(['error' => 'Credenciales inválidas'], 401);
    //     }

    // }


    #[Route('/new', methods: ['POST'])]

    /**
     * Summary of new
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\UserRepository $userRepository
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function new(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Decode the JSON content from the request body into an associative array.
        $data = json_decode($request->getContent(), true);

        // Validate that all required data ('user', 'password', 'name') is present in the request body.
        // If any required field is missing, return a 400 Bad Request error.
        if (!isset($data['user']) || !isset($data['password']) || !isset($data['name'])) {
            return $this->json(['error' => 'Missing "user", "password", or "name" in request body'], 400);
        }

        // Extract the user, password, and name from the decoded request data.
        $username = $data['user'];
        $password = $data['password'];
        $name = $data['name'];

        // Check if a user with the provided username already exists in the database.
        $existingUser = $userRepository->findOneBy(['user' => $username]);
        if ($existingUser) {
            // If a user with this username exists, return a 400 Bad Request error,
            // indicating that the username is already taken.
            return $this->json(['error' => 'User with this username already exists'], 400);
        }

        // Create a new instance of the User entity (representing a nurse in this context).        
        $nurse = new User();
        // Set the user's username.
        $nurse->setUser($username);
        // Set the user's password.
        $nurse->setPassword($password);
        // Set the user's name
        $nurse->setName($name);

        // Persist the new user (nurse) to the database using Doctrine's EntityManager.
        try {
            // Tell Doctrine to manage this new entity, preparing it for insertion.
            $entityManager->persist($nurse);
            // Execute all pending database operations (like inserts, updates) to the actual database.
            $entityManager->flush();

            // If the nurse is created successfully, return a 201 Created response
            // along with a success message and the ID of the newly created nurse.
            return $this->json(['message' => 'Nurse created successfully', 'id' => $nurse->getId()], 201);
        } catch (\Exception $e) {
            // Catch any unexpected exceptions that might occur during the database operation
            // (e.g., database connection issues, constraint violations not caught by previous checks).
            // Return a 500 Internal Server Error with the exception message for debugging.
            return $this->json(['error' => 'Failed to create nurse: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/find/{id}', name: 'get_nurse_by_id', methods: ['GET'])]
    public function getNurseById(int $id, UserRepository $userRepository): JsonResponse
    {
        // Search for the nurse by their ID in the database
        $nurse = $userRepository->find($id);

        // If it doesn't exist, return a 404 error
        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found'], 404);
        }
        
        // If it exists, return the nurse's data with a 200 OK status code
        return $this->json([
            'id' => $nurse->getId(),
            'user' => $nurse->getUser(),
            'name' => $nurse->getName(),
            'password' => $nurse->getPassword()
        ], 200);
    }

    #[Route('/update/{id}', name: 'app_nurse_update', methods: ['PUT'])]
    // Update nurse details
    /**
     * Summary of update
     * @param int $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\UserRepository $userRepository
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $nurse = $userRepository->find($id);
        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found'], 404);
        }

        if (isset($data['user'])) {
            $nurse->setUser($data['user']);
        }
        if (isset($data['password'])) {
            $nurse->setPassword($data['password']);
        }
        if (isset($data['name'])) {
            $nurse->setName($data['name']);
        }

        try {
            $entityManager->persist($nurse);
            $entityManager->flush();

            return $this->json(['message' => 'Nurse updated successfully'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update nurse: ' . $e->getMessage()], 500);
        }
    }
    #[Route('/delete/{id}', name: 'user_delete', methods: ['DELETE'])]
        /**
         * Deletes a user by ID.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @param \App\Repository\UserRepository $userRepository
         * @param \Doctrine\ORM\EntityManagerInterface $entityManager
         * @param int $id
         * @return JsonResponse
         */
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
        {
            // Attempt to find the user by the given ID.
            $user = $userRepository->find($id);

            // If no user is found, return a 404 Not Found response.
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }

            try {
                // Remove the user entity from the database.
                $entityManager->remove($user);
                $entityManager->flush();

                // Return a 200 OK response indicating successful deletion.
                return $this->json(['message' => 'User deleted successfully'], 200);
            } catch (\Exception $e) {
                // Catch any database or internal error and return a 500 response.
                return $this->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
            }
        }
}


    
