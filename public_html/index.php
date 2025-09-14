<?php
require __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\ArticleController;
use App\Controllers\LoginController;
use App\Controllers\AdminController;
use App\Controllers\ProfileController;
use App\Controllers\ApiController;

$router = new Router();

$router->post('/api/preview', [new ApiController(), 'preview']);
$router->post('/api/autosave', [new ApiController(), 'autosave']);
$router->post('/api/upload-image', [new ApiController(), 'uploadImage']);
$router->get('/api/users', [new ApiController(), 'users']);

$router->get('/', [new HomeController(), 'index']);
$router->get('/index.php', [new HomeController(), 'index']);

$router->get('/edit', [new HomeController(), 'editDraft']);
$router->post('/edit', [new HomeController(), 'editDraft']);

$router->post('/article/{id:\d+}/edit', [new ArticleController(), 'edit']);
$router->get('/article/{id:\d+}/edit', [new ArticleController(), 'edit']);
$router->post('/article/{id:\d+}/delete', [new ArticleController(), 'delete']);

$router->post('/approve', [new ArticleController(), 'approve']);
$router->post('/restore', [new ArticleController(), 'restore']);

$router->get('/article/{slug}', [new ArticleController(), 'show']);
$router->get('/article', [new ArticleController(), 'show']);

$router->get('/login', [new LoginController(), 'login']);
$router->post('/login', [new LoginController(), 'login']);
$router->get('/logout', [new LoginController(), 'logout']);
$router->get('/register', [new LoginController(), 'register']);
$router->post('/register', [new LoginController(), 'register']);
$router->get('/verify', [new LoginController(), 'verify']);

$router->get('/admin', [new AdminController(), 'dashboard']);
$router->get('/admin/categories', [new AdminController(), 'categories']);
$router->get('/admin/tags', [new AdminController(), 'tags']);
$router->get('/admin/create', [new AdminController(), 'create']);
$router->get('/admin/edit', [new AdminController(), 'edit']);
$router->get('/admin/search', [new AdminController(), 'search']);
$router->get('/admin/logs', [new AdminController(), 'logs']);
$router->get('/users', [new AdminController(), 'users']);
$router->post('/users', [new AdminController(), 'users']);
$router->post('/admin/categories', [new AdminController(), 'categories']);
$router->post('/admin/tags', [new AdminController(), 'tags']);
$router->post('/admin/edit', [new AdminController(), 'edit']);
$router->post('/admin/create', [new AdminController(), 'create']);
$router->post('/admin/search', [new AdminController(), 'search']);

$router->get('/profile', [new ProfileController(), 'show']);
$router->get('/change-password', [new ProfileController(), 'changePassword']);
$router->post('/change-password', [new ProfileController(), 'changePassword']);

$router->get('/my-articles', [new HomeController(), 'myArticles']);
$router->get('/my-drafts', [new HomeController(), 'myDrafts']);
$router->post('/submit-draft', [new HomeController(), 'submitDraft']);

$router->get('/submit', [new HomeController(), 'submitForm']);
$router->post('/submit', [new HomeController(), 'submitForm']);

$router->dispatch();
