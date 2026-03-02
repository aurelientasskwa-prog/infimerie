function toggleMenu(){
    let menu = document.getElementById("menu");
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

document.getElementById("formEtudiant").addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);

    fetch("backend/ajouter_etudiant.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById("message").innerHTML = data;
        document.getElementById("message").style.color = "green";
        this.reset();
    })
    .catch(() => {
        document.getElementById("message").innerHTML = "Erreur d'envoi !";
        document.getElementById("message").style.color = "red";
    });
});