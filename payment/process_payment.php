<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'];
        $orderId = $data['order_id'];
        
        // Create payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => ['order_id' => $orderId]
        ]);
        
        echo json_encode([
            'success' => true,
            'clientSecret' => $paymentIntent->client_secret,
            'paymentIntentId' => $paymentIntent->id
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>