


/** @type {HTMLCanvasElement} */
const arretsCanvas = document.getElementById("arretCanvas");
let arretCtx = arretsCanvas.getContext("2d");
const BusCanvas = document.getElementById("entityCanvas");
let BusCtx = arretsCanvas.getContext("2d");
let sizeX = arretsCanvas.width;
let sizeY = 100;
const canvasRect = arretsCanvas.getBoundingClientRect();
let ratio = 1;
let mouseX = null;
let mouseY = null;
let currentArretIndex = 0;

document.addEventListener("mousemove", placementCursor)

function placementCursor(e){
  mouseX = (e.clientX - canvasRect.left);
  mouseY = (e.clientY - canvasRect.top);
  renderStops();
}

let arrets = {};
let buses = [];


  function setup(conf) {
    ratio = fixRatio(arretsCanvas);
    //fixRatio(BusCanvas);
    setArret(conf);   
    setLinks(conf);
    console.log(conf);
    setBuses(conf.bus, conf.buses, conf.parcours);
    setCoordinates();
    arretsCanvas.parentNode.addEventListener("click", () => {
      Object.values(arrets).forEach((arret) => {
        console.log(arret);
        if(arret.currentColor == arret.hoverColor){ arret.infoPopUp(); }
      });
    });
    }

  function setArret(json){
    for (let arret in json["arrets"]){
        arrets[arret] = (new Arret(arret,arretCtx));
    }
  }

  function setLinks(json){
    let routes=json["routes"]
      Object.keys(arrets).forEach((arret) => {
          for (let route in routes){
              route = routes[route];
              if(route["arrets"][0]==arret){
                  arrets[arret].addLink(route["arrets"][1],route["distance"]);
              }
              if(route["arrets"][1]==arret){
                arrets[arret].addLink(route["arrets"][0],route["distance"]);
            }
          }
        });
        }      

function setBuses(busTypeJSON, bussesJSON, parcoursJSON){
  Object.values(bussesJSON).forEach((bus) => {
    buses.push(new Bus(busTypeJSON[bus["type"]],parcoursJSON[bus.parcours],arrets,BusCtx));
  });
}




  function setCoordinates() {
    const arretArray = Object.values(arrets);
    setTimeout(() => {
    addClickListenerForArret(arretArray[currentArretIndex]);
    }, 500);
  }
  
  function addClickListenerForArret(arret) {
    showPopup("Arrêt " + arret.nom, "Placez l'arrêt " + arret.nom + " en cliquant sur le canvas.");
    function handleClick(event) {
      event.stopPropagation();
  
      arret.setX(mouseX/ratio);
      arret.setY(mouseY/ratio);
  

      arretsCanvas.parentNode.removeEventListener("click", handleClick, true);
  

      currentArretIndex++;
      if (currentArretIndex < Object.values(arrets).length) {
        addClickListenerForArret(Object.values(arrets)[currentArretIndex]);
      }
    }
  
    arretsCanvas.parentNode.addEventListener("click", handleClick, { capture: true, once: true });
    renderStops();
    buses.forEach((bus)=>{bus.setPosition();})    
  }
        


function renderStops() {    
    Object.values(arrets).forEach((arret) => {

      let links = Object.keys(arret.links);

      links.forEach((link) => {

      arretCtx.beginPath();
      arretCtx.moveTo(arret.x, arret.y);
      arretCtx.lineTo(arrets[link].x, arrets[link].y);
      arretCtx.stroke();
      });
    });
      Object.values(arrets).forEach((arret) => {
      arret.draw();
      arret.checkHover(mouseX, mouseY);
      renderBuses();
    });
  }

  function renderBuses(){
    buses.forEach((bus)=>{
      bus.render();
    });
  }

function fixRatio(Canvas){
  let dimensions = getObjectFitSize(true,Canvas.clientWidth,Canvas.clientHeight,Canvas.width,Canvas.height);
  Canvas.width = dimensions.width;
  Canvas.height = dimensions.height;

  let ctx = Canvas.getContext("2d");
  let ratio = Math.min(
    Canvas.clientWidth / sizeX,
    Canvas.clientHeight / sizeY
  );
  sizeX = Canvas.clientWidth;
  sizeY = Canvas.clientHeight;
  ctx.scale(ratio, ratio); //adjust this!
  return ratio;
}

  function getObjectFitSize(contains /* true = contain, false = cover */,containerWidth,containerHeight,width,height) {
    let doRatio = width / height;
    let cRatio = containerWidth / containerHeight;
    let targetWidth = 0;
    let targetHeight = 0;
    let test = contains ? doRatio > cRatio : doRatio < cRatio;
  
    if (test) {
      targetWidth = containerWidth;
      targetHeight = targetWidth / doRatio;
    } else {
      targetHeight = containerHeight;
      targetWidth = targetHeight * doRatio;
    }
  
    return {
      width: targetWidth,
      height: targetHeight,
      x: (containerWidth - targetWidth) / 2,
      y: (containerHeight - targetHeight) / 2
    };
  }

  function increment(){
    BusCtx.clearRect(0, 0, BusCanvas.width, BusCanvas.height);    
    buses.forEach((bus) => {bus.increment()});
    renderStops();    
  }