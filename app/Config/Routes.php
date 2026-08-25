<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

/*
 |--------------------------------------------------------------------------
 | Personal Task Management System — Routes
 |--------------------------------------------------------------------------
 */

// Auth routes (no filter needed)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::doRegister');

// Protected routes (require auth filter)
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');

    // Spaces
    $routes->get('spaces', 'Space::index');
    $routes->post('spaces', 'Space::store');
    $routes->put('spaces/(:num)', 'Space::update/$1');
    $routes->delete('spaces/(:num)', 'Space::delete/$1');
    $routes->get('spaces/(:num)/projects', 'Project::index/$1');

    // Projects
    $routes->post('projects', 'Project::store');
    $routes->put('projects/(:num)', 'Project::update/$1');
    $routes->delete('projects/(:num)', 'Project::delete/$1');
    $routes->get('projects/(:num)/tickets', 'Ticket::index/$1');

    // Tickets
    $routes->post('tickets', 'Ticket::store');
    $routes->put('tickets/(:num)', 'Ticket::update/$1');
    $routes->delete('tickets/(:num)', 'Ticket::delete/$1');
    $routes->post('tickets/(:num)/status', 'Ticket::updateStatus/$1');

    // Sessions
    $routes->get('sessions', 'Session::index');
    $routes->get('sessions/create', 'Session::create');
    $routes->post('sessions', 'Session::store');
    $routes->get('sessions/(:num)/active', 'Session::active/$1');
    $routes->post('sessions/(:num)/end', 'Session::end/$1');
    $routes->post('sessions/tick', 'Session::tickTicket');
    $routes->get('sessions/(:num)', 'Session::detail/$1');

    // API — cascading dropdown for session creation
    $routes->get('api/spaces/(:num)/projects', 'Project::apiBySpace/$1');
    $routes->get('api/spaces-tree', 'Space::apiTree');
});
