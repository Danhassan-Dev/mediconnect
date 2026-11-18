 if ($user['status'] !== 'active') {
        $error = "Your account is not active.";
    } else {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {

            case 'admin':
                header("Location: admin_dashboard.php");
                exit;

            case 'doctor':
                header("Location: doctor_dashboard.php");
                exit;

            case 'patient':
                header("Location: dashboard.php");
                exit;

            default:
                $error = "Invalid account role.";
        }
    }
}