export default class Arret {

constructor(busTypeJSON, parcours, ctx){
    this.type = busTypeJSON.type;
    this.capacite = busTypeJSON["capacite-max"];
    this.vitesseChargement = busTypeJSON["vitesse-chargement"];
    this.vitesseDeplacement = busTypeJSON["vitesse-deplacement"];
    this.parcours = Object.values(parcours);
    this.arretCourrant = 0;
    this.prochaineArret = 1;
    this.progression = 0;
    this.distanceProchaineArret;
    this.distancePatcourru = 0;
    this.pourcentageProgression = 0;
    this.ctx = ctx;
}

setDistanceProchaineArret(arrets){
    this.distanceProchaineArret = arrets[this.parcours[this.arretCourrant].links[this.parcours[this.prochaineArret]]];
    this.distancePatcourru = 0;
    this.pourcentageProgression = 0;
}

increment(){
    this.distanceProchaineArret -= this.vitesseDeplacement;
}

}