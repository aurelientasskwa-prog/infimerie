<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #ffffff;
      font-family: Arial, sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      width: 350px;
      padding: 20px;
      box-sizing: border-box;
      text-align: center;
      max-width: 350px;          /* largeur max du bloc */
      display: flex;
      flex-direction: column;    /* empile logo, titre, champs, bouton */
      align-items: center;       /* centre chaque élément */

    }

    .header {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 30px;
    }

    .header img {
      width: 120px; 
      height: auto;
      margin-bottom: 15px;
    }

    .header h1 {
      font-weight: bold;
      color: #539EFF;
      margin: 0;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .form-group label {
      font-style: italic;
      font-weight: 500;
      display: flex;
      align-items: center;
      margin-bottom: 5px;
    }

    .form-group label img {
      width: 20px; 
      height: auto;
      margin-right: 8px;
    }

    .form-group input {
      width: 500px; 
      height: 40px; 
      padding: 8px;
      background-color: #F6FAFE;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
      border: none; 
      box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
      margin: 0 auto; 
      display: block; 

    }

  button {
  width: 300px;
  height: 60px;
  font-weight: bold;
  font-size: 20px;
  background-color: #539EFF;
  color: #ffffff;
  border: none;
  border-radius: 80px; /* bordure arrondie */
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0px 7px 4px rgba(0, 0, 0, 0.25); /* drop shadow */
}

    button:hover {
      background-color: darkblue;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="header">
      <img src="logo.png" alt="Logo">
      <h1>Connexion</h1>
    </div>

    <form action="accueil.html" method="POST">
      <div class="form-group">
        <label>
          <img src="user-icon.png" alt="Utilisateur">
          Entrer votre nom:
        </label>
        <input type="text" name="username">
      </div>

      <div class="form-group">
        <label>
          <img src="lock-icon.png" alt="Mot de passe">
          Votre mot de passe:
        </label>
        <input type="password" name="password">
      </div>

      <button type="submit" >Se connecter</button>
    </form>
  </div>

</body>
</html>