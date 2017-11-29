<?php

require_once '../vendor/autoload.php';
/**
 * L'objet application représente le site. C'est l'objet principal de Silex par lequel nous passerons pratiquement tout le temps pour déployer de nouvelles fonctionnalités
 */
$app = new \Silex\Application();

require_once '../config/db.php';


/**
 * La méthode get de l'obket Application permet de dire à Silex d'exécuter un code spécifique si une uri est atteinte par la méthode http GET. Ce code est inclus dans une fonction ou n'importe quel autre callable.
 */  
$app->get('/home', function(\Silex\Application $app){
    return $app['twig']->render('home.html.twig');
})->bind('home');

// on crée une deuxième route associée à l'uri /listusers
$app->get('/listusers', function(\Silex\Application $app){
    /**
     * Je récupère une liste d'utilisateurs grâce à mon modèle UserDAO
     */
    $users = $app['users.dao']->findMany();
    /**
     * Ma liste d'utilisateurs est transmise à mon template au moyen d'un tableau associatif
     */
    return $app['twig']->render('listusers.html.twig',['users'=>$users]);
})->bind('listusers');

$app->get('/profile/{id}', function($id, \Silex\Application $app){
    $user = $app['users.dao']->find($id);
    return $app['twig']->render('profile.html.twig',['user'=>$user]);
 })->bind('profile');

//on accede a $app comme si c'etait un tableau alors que c'est un objet.  En php, cet objet implement une interface array access. une fois implementée, cette interface permet d'acceder a l'objet comme si c'etait un tableau. i enotre dao sera accessible dans notre app, a la clé $app['suers.dao']. pour raisons de lisibilité, compacité du code
//un service est une classe accessible durant tte durée de vie de l'app a un certain index'
//la classe Application implémente une interface spéciale propre a PHP, appelée ArrayAccess. Cette interface permet d'utiliser notre objet comme si il s'agissait d'un tableau. L'objet conserve malgré tt ses caractéristiques d'objet (méthodes, champs...)
//ie si qqu'(un fait appel à $app['users.dao'], tu instancies un objet. On instancie pas l'objet par défaut. ça fait un effet de singleton. du coup, on peut se passer du PDOSingleton.
// on asse par une fonction au lieu d'instancier directement ntoer objet afin de n'instancier notre service qu'une seule fois. et seulement si nécessaire.
//a la premiere fois, $app[users.dao] est une fonction. ttes les fois suivantes : $app[users.dao]  = new DAO()
//ça, ça depend de silex.
//on a délaré (ou créé) un service.
$app['users.dao'] = function($app) {
    return new \DAO\UserDAO($app['pdo']);
};

$app['pdo'] = function($app){
    $options = $app['pdo.options'];
    return new \PDO("{$options['sgbdr']}://host={$options['host']};dbname={$options['dbname']};charset={$options['charset']}", $options['username'], $options['password'], array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));
};

/**
 * Les services peuvent être enregistrer via des Services Providers qui sont des classes dont l'unique but est de déclarer des services.
 */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/views',
    'twig.options' => array(
        'debug' => true
        )
));

// Pour lancer l'application il ne faut pas oublier d'appeler la méthode run de app
$app->run();

