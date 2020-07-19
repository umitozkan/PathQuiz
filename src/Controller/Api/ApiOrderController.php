<?php

namespace App\Controller\Api;


use App\Entity\User;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Order;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


/**
 * @Route("/order")
 */
class ApiOrderController extends AbstractController
{
    /**
     * @Route("/create", name="api_order_create",  methods={"POST"})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function create(Request $request)
    {

        $data = json_decode(
            $request->getContent(),
            true
        );

        $entityManager = $this->getDoctrine()->getManager();
        $order = new Order();
        $current_user = $this->getUser();

        $userId = $current_user->getId();
        $order->setUserId($userId);
        $order->setOrderCode(uniqid("Order_"));
        $order->setAddress($data["address"]);
        $order->setProductId($data["product_id"]);
        $order->setQuantity($data["quantity"]);

        $entityManager->persist($order);
        $entityManager->flush();
        $order = json_encode([
            "resultCode" => 1,
            "resultMessage" => "Siparinişiniz {$order->getOrderCode()} kodu ile alınmıştır."
        ]);
        $response = $this->get('serializer')->serialize($order, 'json');
        $response = new Response(json_decode($response));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    /**
     * @Route("/update/{id}", name="api_order_update",  methods={"GET"})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function update(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $order = $this->getDoctrine()->getRepository(Order::class)->find($request->get("id"));

        if (!$order) {
            $order = json_encode([
                "resultCode" => -1,
                "resultMessage" => "Sipariş Bulunamadı"
            ]);
            $response = $this->get('serializer')->serialize($order, 'json');
            $response = new Response(json_decode($response));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        if (!$order->getShippingDate()) {
            $data = json_decode(
                $request->getContent(),
                true
            );
            $order->setProductId($data["product_id"]);
            $order->setQuantity($data["quantity"]);
            $order->setAddress($data["address"]);

            $entityManager->flush();
            $order = json_encode([
                "resultCode" => 1,
                "resultMessage" => "{$order->getOrderCode()} kodlu siparişiniz ile güncellenmmiştir."
            ]);
            $response = $this->get('serializer')->serialize($order, 'json');
            $response = new Response(json_decode($response));
            $response->headers->set('Content-Type', 'application/json');
            return $response;

        } else {
            $order = json_encode([
                "resultCode" => -1,
                "resultMessage" => "Siparişiniz kargoya verildiği için güncelleyemezsiniz."
            ]);
            $response = $this->get('serializer')->serialize($order, 'json');
            $response = new Response(json_decode($response));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


    }

    /**
     * @Route("/{id}}", name="api_order_detail",  methods={"POST"})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detail(Request $request)
    {
        return JsonResponse::create("detail");

    }

    /**
     * @Route("/list", name="api_order_list",  methods={"GET"})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function list(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Order::class);
        $current_user = $this->getUser();

        $userId = $current_user->getId();
        $orders = $repository->findBy(["user_id" => $userId]);
        $orders = $this->get('serializer')->serialize($orders, 'json');

        $response = new Response($orders);
        //var_dump($orders);
        $response->headers->set('Content-Type', 'application/json');

        return $response;


    }

}
