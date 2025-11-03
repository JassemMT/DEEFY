<?php

namespace iutnc\deefy\action;

class DefaultAction extends Action{
    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __construct()
    {

        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    public function execute(): string{
        if(isset($_SESSION['user'])){
        return '<h1>Bienvenue ' . substr($_SESSION['user']['email'], 0, strpos($_SESSION['user']['email'], '@')) . ' !</h1>';
        } else {
            return '<h1>Bienvenue sur Deefy !</h1>
            <p>Veuillez vous connecter ou créer un compte pour accéder à toutes les fonctionnalités.</p>';
        }
    }

}
