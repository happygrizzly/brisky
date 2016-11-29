<?php 

    $app->register(new Silex\Provider\SessionServiceProvider());

    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\User;

    $app->register(new Silex\Provider\SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'login' => array(
                'pattern' => '^/login$',
            ),
            'secured' => array(
                'pattern' => '^.*$',
                'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
                'logout' => array('logout_path' => '/logout', 'invalidate_session' => true),
                'users' => array(
                    'admin' => array('ROLE_ADMIN', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'),
                    'user' => array('ROLE_USER', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'),
                    'viewer' => array('ROLE_VIEWER', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a')
                ),
            ),
        )
    ));

    $app['security.role_hierarchy'] = array(
        'ROLE_ADMIN' => array('ROLE_USER'),
    );
    
    $app['security.access_rules'] = array(
        array('^/admin', 'ROLE_ADMIN'),
        array('^.*$', ['ROLE_USER', 'ROLE_VIEWER']),
    );
    
?>