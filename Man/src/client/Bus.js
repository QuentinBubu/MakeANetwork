class Bus {

constructor(busTypeJSON, parcours,arrets, ctx){
    this.type = busTypeJSON.type;
    this.capacite = busTypeJSON["capacite-max"];
    this.vitesseChargement = busTypeJSON["vitesse-chargement"];
    this.vitesseDeplacement = busTypeJSON["vitesse-deplacement"];
    this.parcours = [];
    parcours.forEach(stop => {
        this.parcours.push([arrets[stop],-1])
    });
    this.arretCourrant = 0;
    this.distanceParcouru = 0;
    this.pourcentageProgression = 0;
    this.setParcoursDistances();
    this.distanceProchaineArret=this.parcours[this.arretCourrant][1];
    this.radius = 2;
    this.ctx = ctx;
    this.x;
    this.y;
}

setParcoursDistances(){
    for (let index = 0; index<this.parcours.length -1; index++){
        this.parcours[index][1] = this.parcours[index][0].links[this.parcours[index+1][0].nom]
    }   
}

setPosition(){
    let current = this.parcours[this.arretCourrant][0];
    let next = this.parcours[this.arretCourrant + 1][0];
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
    this.distanceParcouru += this.vitesseDeplacement;
    this.pourcentageProgression = this.distanceProchaineArret/this.distanceParcouru;
    if(this.distanceParcouru >= this.distanceProchaineArret){
        this.distanceParcouru = 0;
        this.pourcentageProgression = 0;
        if(this.arretCourrant + 1 < this.parcours.length - 1){        
            this.arretCourrant++   
        }
        else{
            this.arretCourrant = 0;
        }
        this.distanceProchaineArret=this.parcours[this.arretCourrant][1];        
    }
    this.setPosition();
    this.render();
}

}