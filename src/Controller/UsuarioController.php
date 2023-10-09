<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Direccion;
use Symfony\Component\HttpFoundation\Request;

#[Route('/usuario', name: 'usuarios')]
class UsuarioController extends AbstractController
{

    #[Route('', name: 'app_usuario_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $usuario = new Usuario();
        $usuario->setNombre($request->request->get('nombre'));
        $usuario->setEdad($request->request->get('edad'));
        $edad = $request->request->get('edad');
        if($edad <= 0){
            return $this->json(['error'=>'Edad menor o igual a cero:'.$edad], 422);
        }
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($usuario);
        $direccion = new Direccion($usuario);
        $direccion->setDepartamento($request->request->get('departamento'));
        $direccion->setMunicipio($request->request->get('municipio'));
        $direccion->setDireccion($request->request->get('direccion'));
        $direccion->setUsuario($usuario);
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->persist($direccion);
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();
        return $this->json(['message' => 'Se guardo el nuevo usuario con id ' .$usuario->getId()
        ]);

    }

    #[Route('', name: 'app_usuario_read_all', methods: ['GET'])]
    public function readAll(EntityManagerInterface $entityManager):JsonResponse{
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();
        $direcciones = $entityManager->getRepository(Direccion::class)->findAll();
        $data = [];
        
        foreach ($usuarios as $usuario) {
            $data[] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'edad' => $usuario->getEdad(),
            ];
        }
        foreach ($direcciones as $direccion) {
            $data1[] = [
                'departamento' => $direccion->getDepartamento(),
                'municipio' => $direccion->getMunicipio(),
                'direccion' => $direccion->getDireccion(),
            ];
        }
        return $this->json(array_merge($data, $data1));
    }
    
    #[Route('/{id}', name: 'app_usuario_read_one', methods: ['GET'])]
    public function readOne(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);
        if(!$usuario){
            return $this->json(['error'=>'No se encontro el usuario.'],404);
        }
        $data[] = [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'edad' => $usuario->getEdad(),
        ];

        $direccion = $entityManager->getRepository(Direccion::class)->find($id);
        if(!$direccion){
            return $this->json(['error'=>'No se encontro la direccion de ese usuario.'],404);
        }
        $data1[] = [
            'departamento' => $direccion->getDepartamento(),
            'municipio' => $direccion->getMunicipio(),
            'direccion' => $direccion->getDireccion(),
        ];
        return $this->json(array_merge($data, $data1));
}

    #[Route('/{id}', name: 'app_usuario_edit', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {
        // Busca el usuario por id
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);
        // Si no lo encuentra responde con un error 404
        if (!$usuario) {
            return $this->json(['error'=>'No se encontro el usuario con id:'.$id], 404);
        }
        // Obtiene los valores del body de la request
        $nombre = $request->request->get('nombre');
        $edad = $request->request->get('edad');
        
        // Se actualizan los datos a la entidad
        $usuario->setNombre($nombre);
        $usuario->setEdad($edad);
        if($edad <= 0){
            return $this->json(['error'=>'Edad menor o igual a cero:'.$edad], 422);
        }
        $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(),'edad' => $usuario->getEdad()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json(['message'=>'Se actualizaron los datos del usuario.', 'data' => $data]);
    }

    #[Route('/{id}', name: 'app_usuario_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {
        //Busca el usuario por id
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);
        // Si no lo encuentra responde con un error 404
        if (!$usuario) {
            return $this->json(['error'=>'No se encontro el usuario con id:'.$id], 404);
        }
        // Remueve la entidad
        $entityManager->remove($usuario);
        $data=['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(),'edad' => $usuario->getEdad()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json(['message'=>'Se elimino el usuario.', 'data' =>$data]);
    }
}
