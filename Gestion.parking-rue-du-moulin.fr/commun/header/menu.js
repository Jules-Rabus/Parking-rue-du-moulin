document.addEventListener('DOMContentLoaded', init,false);
console.log("Lancement du programme 'menu.js'")

function init(){                               
                                                                         
    document.getElementById('ouverture_menu').addEventListener("click", menu_changement); 
    document.getElementById('fermeture_menu').addEventListener("click", menu_changement);	
	
}

function menu_changement(){

    if (document.querySelectorAll("#ferme").length > 0){ 
        document.getElementsByClassName("menu")[0].id = "ouvert";
        console.log("Ouverture du menu");
    }

    else{
        document.getElementsByClassName("menu")[0].id = "ferme";
        console.log("Fermeture du menu");
    }
}
