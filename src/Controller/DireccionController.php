<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Direccion;
use Symfony\Component\HttpFoundation\Request;

#[Route('/direccion', name: 'direcciones')]
class DireccionController extends AbstractController
{
    #[Route('', name: 'app_usuario_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, Usuario $usuario1): JsonResponse
    {
        $direccion = new Direccion();
        $direccion->setDepartamento($request->request->get('departamento'));
        $direccion->setMunicipio($request->request->get('municipio'));
        $direccion->setDireccion($request->request->get('direccion'));
        $direccion->setUsuario($usuario1);
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($direccion);
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();
        return $this->json(['message' => 'Se guardo la nueva direccion del usuario', $direccion->getUsuario()
        ]);
    }

    #[Route('', name: 'app_usuario_read_all', methods: ['GET'])]
    public function readAll(EntityManagerInterface $entityManager):JsonResponse{
        $direcciones = $entityManager->getRepository(Direccion::class)->findAll();
        $data = [];
        
        foreach ($direcciones as $direccion) {
            $data[] = [
                'departamento' => $direccion->getDepartamento(),
                'municipio' => $direccion->getMunicipio(),
                'direccion' => $direccion->getDireccion(),
                'usuario' => $direccion->getUsuario(),
            ];
        }
        return $this->json($data);
    }
    
    #[Route('/{usuario}', name: 'app_usuario_read_one', methods: ['GET'])]
    public function readOne(EntityManagerInterface $entityManager, Usuario $usuario1): JsonResponse
    {
        $direccion = $entityManager->getRepository(Direccion::class)->find($usuario1);
        if(!$direccion){
            return $this->json(['error'=>'No se encontro la direccion de ese usuario.'],404);
        }
        return $this->json([
            'departamento' => $direccion->getDepartamento(),
            'municipio' => $direccion->getMunicipio(),
            'direccion' => $direccion->getDireccion(),
            'usuario' => $direccion->getUsuario(),
        ]);
    }

    #[Route('/{usuario}', name: 'app_usuario_edit', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, Usuario $usuario1, Request $request): JsonResponse
    {
        // Busca el usuario por id
        $direccion = $entityManager->getRepository(Direccion::class)->find($usuario1);
        // Si no lo encuentra responde con un error 404
        if (!$direccion) {
            return $this->json(['error'=>'No se encontro la direccion del usuario:'.$usuario1->getId()], 404);
        }
        // Obtiene los valores del body de la request
        $departamento = $request->request->get('departamento');
        $municipio = $request->request->get('municipio');
        $direccion1 = $request->request->get('direccion');
        // Si no envia uno responde con un error 422
        if ($departamento == null || $municipio == null || $direccion1){
            return $this->json(['error'=>'Se debe enviar la direccion, el municipio y el departamento del usuario.'], 422);
        }
        // Se actualizan los datos a la entidad
        $direccion->setDepartamento($departamento);
        $direccion->setMunicipio($municipio);
        $direccion->setDireccion($direccion);
        $data=['departamento' => $direccion->getDepartamento(), 'municipio' => $direccion->getMunicipio(),'direccion' => $direccion->getDireccion(), 'usuario'=> $direccion->getUsuario()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json(['message'=>'Se actualizaron los datos de la direccion.', 'data' => $data]);
    }

    #[Route('/{usuario}', name: 'app_usuario_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Usuario $usuario1, Request $request): JsonResponse
    {
        //Busca el usuario por id
        $direccion = $entityManager->getRepository(Direccion::class)->find($usuario1);
        // Si no lo encuentra responde con un error 404
        if (!$direccion) {
            return $this->json(['error'=>'No se encontro la direccion del usuario:'.$usuario1->getId()], 404);
        }
        // Remueve la entidad
        $entityManager->remove($direccion);
        $data=['departamento' => $direccion->getDepartamento(), 'municipio' => $direccion->getMunicipio(),'direccion' => $direccion->getDireccion(), 'usuario'=> $direccion->getUsuario()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json(['message'=>'Se elimino la direccion.', 'data' =>$data]);
    }
}

