class Arret {
  constructor(name, ctx) {
    this.nom = name
    this.x;
    this.y;
    this.radius = 10;
    this.originalColor = "green";
    this.hoverColor = "red";
    this.currentColor = this.originalColor;
    this.isHovered = false;
    this.links = [];
    this.ctx = ctx;
    this.vehiculesEnApproche;
    this.vehiculesEnAttente;
    this.fileAttente;
  }

  setX(x){
    this.x = x;
  }

  setY(y){
    this.y = y;
  }

  addLink(arret,distance){
    this.links[arret] = distance;
  }

  draw() {
    this.ctx.beginPath();
    this.ctx.arc(this.x,this.y, this.radius, 0, 2 * Math.PI, false);
    this.ctx.fillStyle = this.currentColor;
    this.ctx.fill();
    this.ctx.stroke();
  }
  
  checkHover(mouseX,mouseY) {
      this.isHovered = this.ctx.isPointInPath(mouseX, mouseY)
      if (this.isHovered) {
        this.currentColor = this.hoverColor
      }
  
      else {
        this.currentColor = this.originalColor;
      }
    }

    infoPopUp(){
      if(this.vehiculesEnApproche!=undefined){
      let popUpBody = " -vehicules en approche : ";
      Object.keys(this.vehiculesEnApproche).forEach((vehicules) => {
        popUpBody = popUpBody + "bus n°" + vehicules + " dans "  + this.vehiculesEnApproche[vehicules] + " ticks, "
      });

      popUpBody = popUpBody + "; -vehicules en attente : ";
      Object.keys(this.vehiculesEnAttente).forEach((vehicules) => {
        popUpBody = popUpBody + "bus n°" + vehicules + ", "
      });
      
      popUpBody = popUpBody + "; -file d'attente : ";
      this.fileAttente.forEach((personne) => {
        popUpBody = popUpBody + personne[0] + ", "
      });

      showPopup("Arrêt " + this.nom, popUpBody + ";");
    }
    }
  
}
