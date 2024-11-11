class Bus {

constructor(busTypeJSON, parcours,arrets, ctx){
    this.type = busTypeJSON.type;
    this.capacite = busTypeJSON["capacite-max"];
    this.vitesseDeplacement = 1/busTypeJSON["vitesse-deplacement"];
    this.parcours = [];
    this.state;
    parcours.forEach(stop => {
        this.parcours.push([arrets[stop],-1])
    });
    this.arretCourrant = 0;
    this.distanceParcouru = 0;
    this.pourcentageProgression = 0; 
    let retour = this.getShortestPath(arrets,this.parcours[this.parcours.length-1][0],this.parcours[0][0]).path;
    for (let index = 1; index<retour.length; index++){
        this.parcours.push([retour[index],-1]);
    }
    this.setParcoursDistances(arrets);
    this.distanceProchaineArret=this.parcours[this.arretCourrant][1];
    this.radius = 2;
    this.ctx = ctx;
    this.x;
    this.y;
}

setParcoursDistances(arrets){
    for (let index = 0; index<this.parcours.length -1; index++){
        let next = this.parcours[index][0].links[this.parcours[index+1][0].nom];
        if (next==undefined){
            let inserts = this.getShortestPath(arrets,this.parcours[index][0],this.parcours[index+1][0]).path
            for(let insert = 1; insert < inserts.length-1; insert++){
                this.parcours.splice(index+insert,0,[inserts[insert],-1]);                         
            }
            this.setParcoursDistances(arrets);            
            break;       
        }
        else{
            this.parcours[index][1] = next;
        }        
    }   
    
}

getShortestPath(arrets,from,to){
    const distances = {};
    const previous = {};
    const unvisited = [];

    for (let arret of Object.values(arrets)) {
      distances[arret.nom] = Infinity;
      previous[arret.nom] = null;
      unvisited.push(arret);
    }
    distances[from.nom] = 0;

    while (unvisited.length > 0) {
      unvisited.sort((a, b) => distances[a.nom] - distances[b.nom]);

      const current = unvisited.shift();

      if (current.nom === to.nom) break;

      for (let [neighborName, distance] of Object.entries(current.links)) {
        const neighbor = arrets[neighborName];
        const altDistance = distances[current.nom] + distance;

        if (altDistance < distances[neighbor.nom]) {
          distances[neighbor.nom] = altDistance;
          previous[neighbor.nom] = current;
        }
      }
    }

    const path = [];
    let step = to;
    while (step) {        
      path.unshift(step);
      step = previous[step.nom];
    }

    return {
      path: path.length > 1 ? path : null,
      distance: distances[to.nom] !== Infinity ? distances[to.nom] : null
    };
}

setPosition(){
    let current = this.parcours[this.arretCourrant][0];
    if(this.arretCourrant<this.parcours.length-1){        
        let next = this.parcours[this.arretCourrant + 1][0];
        this.x = current.x + (next.x - current.x) * this.pourcentageProgression;
        this.y = current.y + (next.y - current.y) * this.pourcentageProgression;
    }
    else{
        this.x = current.x;
        this.y = current.y;
    }
}

render(){
    this.ctx.beginPath();
    this.ctx.arc(this.x,this.y, this.radius, 0, 2 * Math.PI, false);
    this.ctx.fillStyle = "blue";
    this.ctx.fill();
    this.ctx.stroke();
}

increment(){    
    console.log(this.state);
    if(this.state === "DEPLACEMENT"){        
    this.distanceParcouru += this.vitesseDeplacement;
    this.pourcentageProgression = (this.distanceParcouru/this.distanceProchaineArret);
    if(this.distanceParcouru >= this.distanceProchaineArret){
        this.distanceParcouru = 0;
        this.pourcentageProgression = 0;
        if(this.arretCourrant + 1 < this.parcours.length){        
            this.arretCourrant++;            
        }
        else{
            this.arretCourrant = 0;            
        }
        this.distanceProchaineArret=this.parcours[this.arretCourrant][1];        
    }
    this.setPosition();
}
}

}