<?php
include '../config/database.php';
include '../includes/navbar.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors = [];

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
    if (empty($subject)) $errors['subject'] = 'Subject is required';
    if (empty($message)) $errors['message'] = 'Message is required';

    // If no errors, process the form
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = "Thank you for your message. We'll get back to you soon!";
            
            // Clear form data after successful submission
            $name = $email = $subject = $message = '';
        } catch (PDOException $e) {
            $errors['db'] = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BuyCheaper</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .contact-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .contact-content {
            display: flex;
            gap: 40px;
            margin-top: 30px;
        }

        .contact-info {
            flex: 1;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .contact-form {
            flex: 2;
            padding: 30px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .contact-info h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .contact-info p {
            margin-bottom: 15px;
            color: #666;
        }

        .contact-info i {
            margin-right: 10px;
            color: #007bff;
        }

        @media (max-width: 768px) {
            .contact-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h1>Contact Us</h1>
        
        <div class="contact-content">
            <div class="contact-info">
                <h3>Get in Touch</h3>
                <p><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</p>
                <p><i class="fas fa-envelope"></i> support@buycheaper.com</p>
                <p><i class="fas fa-phone"></i> +880 1234-567890</p>
                
                <h3 style="margin-top: 30px;">Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 4:00 PM</p>
                <p>Sunday: Closed</p>
            </div>

            <div class="contact-form">
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($errors['db'])): ?>
                    <div class="error"><?php echo $errors['db']; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                        <?php if (isset($errors['name'])): ?>
                            <div class="error"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                        <?php if (isset($errors['subject'])): ?>
                            <div class="error"><?php echo $errors['subject']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="error"><?php echo $errors['message']; ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 