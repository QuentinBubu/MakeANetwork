export default class Arret {
  constructor(name, ctx) {
    this.nom = name
    this.x;
    this.y;
    this.radius = 50;
    this.originalColor = "green";
    this.hoverColor = "red";
    this.currentColor = this.originalColor;
    this.isHovered = false;
    this.links = [];
    this.ctx = ctx
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
    }
  
  update() {
    if (this.isHovered) {
      this.currentColor = this.hoverColor
    }

    else {
      this.currentColor = this.originalColor;
    }
  }
}
