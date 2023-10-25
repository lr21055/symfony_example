<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\GeneradorDeMensajes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Producto;
use Symfony\Component\HttpFoundation\Request;

#[Route('/producto', name: 'productos')]
class ProductoController extends AbstractController
{
    #[Route('', name: 'app_producto_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $producto = new Producto();
        $producto->setNombre($request->request->get('nombre'));
        $producto->setPrecio($request->request->get('precio'));
        $producto->setExistencia($request->request->get('existencia'));
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($producto);
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();
        $data = [
            'nombre' => $producto->getNombre(),
        'precio' => $producto->getPrecio(),
        'existencia' => $producto->getExistencia(),
        ];
        return $this->json([$generadorDeMensajes->getMensaje("Se guardó el nuevo producto con nombre", $data)
        ]);
    }

    #[Route('', name: 'app_producto_read_all', methods: ['GET'])]
    public function readAll(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse{
    $repositorio = $entityManager->getRepository(Producto::class);
    $limit = $request->get('limit', 5);
    $page = $request->get('page', 1);
    $productos = $repositorio->findAllWithPagination($page, $limit);
    $total = $productos->count();
    $lastPage = (int) ceil($total/$limit);
    $data = [];
    foreach ($productos as $producto) {
    $data[] = [
        'nombre' => $producto->getNombre(),
        'precio' => $producto->getPrecio(),
        'existencia' => $producto->getExistencia(),
    ];
    }
    return $this->json([$generadorDeMensajes->getMensaje("Los productos registrados son: ", $data), 'total' => $total, 'lastPage'=>
    $lastPage] );
    }
    
    #[Route('/{nombre}', name: 'app_usuario_read_one', methods: ['GET'])]
    public function readOne(EntityManagerInterface $entityManager, string $nombre, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        $producto = $entityManager->getRepository(Producto::class)->find($nombre);
        if(!$producto){
            return $this->json(['error'=>'No se encontro el producto.'],404);
        }
        $data =([
            'nombre' => $producto->getNombre(),
            'precio' => $producto->getPrecio(),
            'existencia' => $producto->getExistencia(),
        ]);
        return $this->json($generadorDeMensajes->getMensaje("El producto solicitado es: ", $data))
    }

    #[Route('/{nombre}', name: 'app_usuario_edit', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, string $nombre, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        // Busca el usuario por id
        $producto = $entityManager->getRepository(Producto::class)->find($nombre);
        // Si no lo encuentra responde con un error 404
        if (!$producto) {
            return $this->json(['error'=>'No se encontro el producto con nombre:'.$nombre], 404);
        }
        // Obtiene los valores del body de la request
        $existencia = $request->request->get('existencia');
        if($existencia <= 0){
            return $this->json(['error'=>'Existencias iguales o menores a cero:'.$existencia], 422);
        }
        $precio = $request->request->get('precio');
        if($precio <= 0){
            return $this->json(['error'=>'Precio igual o menor a cero:'.$precio], 422);
        }
        // Si no envia uno responde con un error 422
        if ($existencia == null || $precio == null){
            return $this->json(['error'=>'Se debe enviar la existencia y el precio del producto.'], 422);
        }
        // Se actualizan los datos a la entidad
        $producto->setExistencia($existencia);
        $producto->setPrecio($precio);
        $data=['nombre' => $producto->getNombre(), 'precio' => $producto->getPrecio(),'existencias' => $producto->getExistencia()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json([$generadorDeMensajes->getMensaje("Se actualizaron los datos del producto", $data)]);
    }

    #[Route('/{nombre}', name: 'app_usuario_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, string $nombre, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
    {
        //Busca el usuario por id
        $producto = $entityManager->getRepository(Producto::class)->find($nombre);
        // Si no lo encuentra responde con un error 404
        if (!$nombre) {
            return $this->json(['error'=>'No se encontro el producto de nombre:'.$nombre], 404);
        }
        // Remueve la entidad
        $entityManager->remove($producto);
        $data=['nombre' => $producto->getNombre(), 'precio' => $producto->getPrecio(),'existencias' => $producto->getExistencia()];
        // Se aplican los cambios de la entidad en la bd
        $entityManager->flush();
        return $this->json([$generadorDeMensajes->getMensaje("Se eliminó el producto: ", $data)]);
    }
}

