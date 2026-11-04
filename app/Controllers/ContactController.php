<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Core\Database;

class ContactController extends Controller
{
    public function index(): void
    {
        SEO::init('contact');
        $this->render('pages.contact', [
            'flash' => $this->getFlash(),
        ], 'main');
    }

    public function send(): void
    {
        $this->verifyCsrf();

        $data = sanitizeInput($_POST, [
            'name'    => 'string',
            'email'   => 'email',
            'subject' => 'string',
            'message' => 'string',
        ]);

        $errors = validateRequired($data, ['name', 'email', 'message']);
        if (!isValidEmail((string) $data['email'])) {
            $errors['email'] = 'Please provide a valid email address.';
        }

        if ($errors) {
            $this->flash('error', 'Please fix the errors and try again.');
            $this->redirectBack();
        }

        // Honeypot check
        if (!empty($_POST['website'])) {
            $this->redirect(url('/contact'));
        }

        // Store in DB
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO contact_submissions (name, email, subject, message, ip) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message'],
            $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        // Send email
        $to      = $_ENV['MAIL_TO'] ?? 'saizulamin@gmail.com';
        $subject = 'New Contact: ' . $data['subject'];
        $body    = "Name: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}";
        $headers = "From: {$data['email']}\r\nReply-To: {$data['email']}";
        @mail($to, $subject, $body, $headers);

        $this->flash('success', 'Message sent! I will get back to you soon.');
        $this->redirect(url('/contact'));
    }
}
