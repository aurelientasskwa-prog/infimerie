<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function login($telephone, $password)
    {
        $sql = "SELECT * FROM infirmerie WHERE contact = :contact";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'contact' => $telephone
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            session_start();
            $_SESSION['idInfirmiere'] = $user['idInfirmiere'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];

            return true;
        }

        return false;
    }
}
