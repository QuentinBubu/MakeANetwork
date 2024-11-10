export default class Arret {

constructor(busTypeJSON, parcours, ctx){
    this.type = busTypeJSON.type;
    this.capacite = busTypeJSON["capacite-max"];
    this.vitesseChargement = busTypeJSON["vitesse-chargement"];
    this.vitesseDeplacement = busTypeJSON["vitesse-deplacement"];
    this.parcours = parcours;
    this.arretCourrant = 0;
    this.prochaineArret = 1;
    this.progression = 0;
    this.distanceProchaineArret;
    this.distanceParcourru = 0;
    this.pourcentageProgression = 0;
    this.radius = 5;
    this.ctx = ctx;
    this.setDistanceProchaineArret();
    this.x;
    this.y;
}

setParcoursDistances(arrets){
    for (let index = 0; index<this.parcours.lenght -2; index++){
        this.parcours.push(this.parcours[index],arrets[this.parcours[index].links[this.parcours[index+1]]])
    }
}

setDistanceProchaineArret(){
    this.distanceProchaineArret = this.parcours[this.arretCourrant];
    this.distancePatcourru = 0;
    this.pourcentageProgression = 0;
}

setPosition(){
    let current = this.parcours[this.arretCourrant];
    let next = this.parcours[this.prochaineArret];
    this.x = current.x + (current.x - next.x) * this.pourcentageProgression;
    this.y = current.y + (current.y - next.y) * this.pourcentageProgression;
}

render(){
    this.ctx.beginPath();
    this.ctx.arc(this.x,this.y, this.radius, 0, 2 * Math.PI, false);
    this.ctx.fillStyle = "blue";
    this.ctx.fill();
    this.ctx.stroke();
}

increment(){
    this.distancePatcourru += this.vitesseDeplacement;
    this.pourcentageProgression = this.distanceProchaineArret/this.distanceParcourru;
    if(this.distanceParcourru >= this.distanceProchaineArret){
        this.distanceParcourru = 0;
        this.pourcentageProgression = 0;
        if(this.prochaineArret < this.parcours.length - 1){        
            this.arretCourrant++
            this.prochaineArret++            
        }
        else{
            this.arretCourrant = 0;
            this.prochaineArret = 1; 
        }
        this.setDistanceProchaineArret();
        this.setPosition();
        this.render();
    }
}

}