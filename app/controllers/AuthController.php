<?php
// AuthController
class AuthController extends Controller {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /** GET /auth/login — Show login page. */
    public function login(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirectByRole($_SESSION['role']);
        }
        $error = $this->getFlash('login_error');
        $this->view('auth.login', ['error' => $error]);
    }

    /** POST /auth/do_login — Process login form. */
    public function doLogin(): void {
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']              ?? '';

        if ($username === '' || $password === '') {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please fill in all fields.'], 422);
            }
            $this->flash('login_error', 'Please fill in all fields.');
            $this->redirect('login');
        }

        $user = $this->userModel->findByUsername($username);

        if ($user === null || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid username or password.'], 401);
            }
            $this->flash('login_error', 'Invalid username or password.');
            $this->redirect('login');
        }

        if (!(int)$user['is_active']) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Your account has been deactivated. Contact the administrator.'], 403);
            }
            $this->flash('login_error', 'Your account has been deactivated. Contact the administrator.');
            $this->redirect('login');
        }


        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['email']     = $user['email'];

        session_regenerate_id(true);


        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $redirectUrl = $this->getRedirectUrl($user['role']);

        if ($this->isAjax()) {
            $this->json([
                'success'  => true,
                'message'  => 'Login successful! Redirecting…',
                'redirect' => BASE_URL . $redirectUrl,
            ]);
        }

        $this->redirect($redirectUrl);
    }

    /** GET /auth/logout — Clear session and redirect. */
    public function logout(): void {
        session_unset();
        session_destroy();
        $this->redirect('login');
    }

    private function getRedirectUrl(string $role): string {
        $map = [
            'admin'           => 'admin/dashboard',
            'asd_coordinator' => 'admin/dashboard',
            'unit_head'       => 'approvals',
            'requester'       => 'requester/dashboard',
            'driver'          => 'driver/dashboard',
        ];
        return $map[$role] ?? '';
    }

    private function redirectByRole(string $role): never {
        $this->redirect($this->getRedirectUrl($role));
    }
}
