<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('artikel', 'Artikel::index');
$routes->get('artikel/(:segment)', 'Artikel::detail/$1');
$routes->get('login', 'Login::index');
$routes->get('sitemap.xml', 'Home::sitemap');
