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

  setX(x) {
    this.x = x;
  }

  setY(y) {
    this.y = y;
  }

  addLink(arret, distance) {
    this.links[arret] = distance;
  }

  draw() {
    this.ctx.beginPath();
    this.ctx.arc(this.x, this.y, this.radius, 0, 2 * Math.PI, false);
    this.ctx.fillStyle = this.currentColor;
    this.ctx.fill();
    this.ctx.stroke();
  }

  checkHover(mouseX, mouseY) {
    this.isHovered = this.ctx.isPointInPath(mouseX, mouseY)
    if (this.isHovered) {
      this.currentColor = this.hoverColor
    }

    else {
      this.currentColor = this.originalColor;
    }
  }

  infoPopUp() {
    if (this.vehiculesEnApproche != undefined) {
      let popUpBody = "- Véhicules en approche : ";
      let vehiculesEnApproche = Object.keys(this.vehiculesEnApproche)
        .map((vehicules) => `Bus n°${vehicules} dans ${this.vehiculesEnApproche[vehicules]} ticks`)
        .join("<br>");
      popUpBody += vehiculesEnApproche + "<hr>- Véhicules en attente : ";

      let vehiculesEnAttente = Object.keys(this.vehiculesEnAttente)
        .map((vehicules) => `Bus n°${vehicules}`)
        .join("<br>");
      popUpBody += vehiculesEnAttente + "<hr>- File d'attente : ";

      let fileAttente = this.fileAttente
        .map((personne) => personne[0])
        .join(", ");
      popUpBody += fileAttente;

      showPopup("Arrêt " + this.nom, popUpBody + ";");
    }
  }

}
