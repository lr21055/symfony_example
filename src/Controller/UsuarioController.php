<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Service\GeneradorDeMensajes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/usuario', name: 'usuarios')]
class UsuarioController extends AbstractController
{
  #[Route('/mayores_a_35', name: 'app_usuario_read_all_older_than_35', methods: ['GET'])]
  public function readAllMayores35(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $usuarios = $entityManager->getRepository(Usuario::class)->findUsuariosMayoresDe35();
    $data = [];
    foreach ($usuarios as $usuario) {
      $data[] = [
        'id' => $usuario->getId(),
        'nombre' => $usuario->getNombre(),
        'edad' => $usuario->getEdad(),
      ];
    }
    return $this->json(['message' => $generadorDeMensajes->getMensaje(0),'data' => $data]);
  }


  #[Route('/mayores_a_35', name: 'app_usuario_read_all_older_than_35', methods: ['GET'])]
  public function readAllNombreConA(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $usuarios = $entityManager->getRepository(Usuario::class)->findNombreConA();
    $data = [];
    foreach ($usuarios as $usuario) {
      $data[] = [
        'id' => $usuario->getId(),
        'nombre' => $usuario->getNombre(),
        'edad' => $usuario->getEdad(),
      ];
    }
    return $this->json(['message' => $generadorDeMensajes->getMensaje(0),'data' => $data]);
  }

  #[Route('', name: 'app_usuario_create', methods: ['POST'])]
  public function create(
    EntityManagerInterface $entityManager,
    Request $request,
    GeneradorDeMensajes $generadorDeMensajes
  ): JsonResponse {
    $usuario = new Usuario();
    $usuario->setNombre($request->request->get('nombre'));
    $usuario->setEdad($request->request->get('edad'));
    // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
    $entityManager->persist($usuario);

    // Se ejecutan las consultas SQL para guardar el nuevo registro
    $entityManager->flush();

    return $this->json([
      'message' => $generadorDeMensajes->getMensaje(0),
      'data' => 'Se guardo el nuevo usuario con id ' . $usuario->getId()
    ]);
  }

  #[Route('', name: 'app_usuario_read_all', methods: ['GET'])]
  public function readAll(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $repositorio = $entityManager->getRepository(Usuario::class);
    $limit = $request->get('limit', 5);
    $page = $request->get('page', 1);
    $usuarios = $repositorio->findAllWithPagination($page, $limit);
    $total = $usuarios->count();
    $lastPage = (int) ceil($total / $limit);
    $data = [];
    foreach ($usuarios as $usuario) {
      $data[] = [
        'id' => $usuario->getId(),
        'nombre' => $usuario->getNombre(),
        'edad' => $usuario->getEdad(),
      ];
    }
    return $this->json(['message' => $generadorDeMensajes->getMensaje(0), 'data' => $data, 'total' => $total, 'lastPage' =>
    $lastPage]);
  }

  #[Route('/{id}', name: 'app_usuario_read_one', methods: ['GET'])]
  public function readOne(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $usuario = $entityManager->getRepository(Usuario::class)->find($id);

    if (!$usuario) {
      return $this->json(['error' => 'No se encontro el usuario.'], 404);
    }

    return $this->json([
      'message' => $generadorDeMensajes->getMensaje(0),
      'data' => NULL,
      'id' => $usuario->getId(),
      'nombre' => $usuario->getNombre(),
      'edad' => $usuario->getEdad()
    ]);
  }

  #[Route('/{id}', name: 'app_usuario_edit', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Busca el usuario por id
    $usuario = $entityManager->getRepository(Usuario::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$usuario) {
      return $this->json(['error' => 'No se encontro el usuario con id: ' . $id], 404);
    }

    // Obtiene los valores del body de la request
    $nombre = $request->request->get('nombre');
    $edad = $request->request->get('edad');

    // Si no envia uno responde con un error 422
    if ($nombre == null || $edad == null) {
      return $this->json(['error' => 'Se debe enviar el nombre y edad del usuario.'], 422);
    }

    // Se actualizan los datos a la entidad
    $usuario->setNombre($nombre);
    $usuario->setEdad($edad);

    $data = ['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'edad' => $usuario->getEdad()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message' => $generadorDeMensajes->getMensaje(0),'data' => 'Se actualizaron los datos del usuario.', 'data' => $data]);
  }

  #[Route('/{id}', name: 'app_usuario_delete', methods: ['DELETE'])]
  public function delete(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Busca el usuario por id
    $usuario = $entityManager->getRepository(Usuario::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$usuario) {
      return $this->json(['error' => 'No se encontro el usuario con id: ' . $id], 404);
    }

    // Remueve la entidad
    $entityManager->remove($usuario);

    $data = ['id' => $usuario->getId(), 'nombre' => $usuario->getNombre(), 'edad' => $usuario->getEdad()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message' => $generadorDeMensajes->getMensaje(0),'data' => 'Se elimino el usuario.', 'data' => $data]);
  }
}
