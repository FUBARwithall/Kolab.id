<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('artikel', 'Artikel::index');
$routes->get('artikel/(:segment)', 'Artikel::detail/$1');
$routes->get('blog', 'Artikel::index');
$routes->get('blog/(:segment)', 'Artikel::detail/$1');
$routes->get('contact', static fn() => redirect()->to('/#demo'));
$routes->get('privacy-policy', static fn() => redirect()->to('/#demo'));
$routes->get('terms-of-service', static fn() => redirect()->to('/#demo'));
$routes->get('login', 'Login::index');
$routes->get('hub', 'Login::index');
$routes->get('sitemap.xml', 'Home::sitemap');
