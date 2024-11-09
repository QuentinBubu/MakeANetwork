import Arret from './Arret.js'
export { setup};


/** @type {HTMLCanvasElement} */
const busCanvas = document.getElementById("busCanvas");
let ctx = busCanvas.getContext("2d");
let sizeX = busCanvas.width;
let sizeY = 100;
const canvasRect = busCanvas.getBoundingClientRect();
let ratio = 1;

let mouseX = null;
let mouseY = null;


document.addEventListener("mousemove", placementCursor)

function placementCursor(e){
  mouseX = (e.clientX - canvasRect.left);
  mouseY = (e.clientY - canvasRect.top);
  renderStops();
}

let arrets = {};


  function setup(conf) {
    ratio = fixRatio(busCanvas);
    setArret(conf);   
    setLinks(conf);
    setCoordinates();
    }

  function setArret(json){
    for (let arret in json["arrets"]){
        arrets[arret] = (new Arret(arret,ctx));
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


  let currentArretIndex = 0;


  function setCoordinates() {
    const arretArray = Object.values(arrets);
    setTimeout(() => {
    addClickListenerForArret(arretArray[currentArretIndex]);
    }, 500);
  }
  
  function addClickListenerForArret(arret) {
    function handleClick(event) {
      event.stopPropagation();
  
      arret.setX(mouseX/ratio);
      arret.setY(mouseY/ratio);
  
      console.log(`Coordinates set for arret: (${mouseX}, ${mouseY})`);
  

      document.removeEventListener("click", handleClick, true);
  

      currentArretIndex++;
      if (currentArretIndex < Object.values(arrets).length) {
        addClickListenerForArret(Object.values(arrets)[currentArretIndex]);
      }
    }
  
    document.addEventListener("click", handleClick, { capture: true, once: true });
    renderStops()
    console.log(arret)
  }
        


function renderStops() {    
    Object.values(arrets).forEach((arret) => {

      let links = Object.keys(arret.links);

      links.forEach((link) => {

      ctx.beginPath();
      ctx.moveTo(arret.x, arret.y);
      ctx.lineTo(arrets[link].x, arrets[link].y);
      ctx.stroke();
      });
    });
      Object.values(arrets).forEach((arret) => {
      arret.draw();
      arret.checkHover(mouseX, mouseY);
    });
  }

function fixRatio(Canvas){
  let dimensions = getObjectFitSize(true,Canvas.clientWidth,Canvas.clientHeight,Canvas.width,Canvas.height);
  Canvas.width = dimensions.width;
  Canvas.height = dimensions.height;

  let ctx = Canvas.getContext("2d");
  console.log(Canvas.clientWidth,Canvas.clientWidth);
  console.log(sizeX,sizeY);
  let ratio = Math.min(
    Canvas.clientWidth / sizeX,
    Canvas.clientHeight / sizeY
  );
  sizeX = Canvas.clientWidth;
  sizeY = Canvas.clientHeight;
  ctx.scale(ratio, ratio); //adjust this!
  console.log(ratio);
  return ratio;
}

  function getObjectFitSize(contains /* true = contain, false = cover */,containerWidth,containerHeight,width,height) {
    var doRatio = width / height;
    var cRatio = containerWidth / containerHeight;
    var targetWidth = 0;
    var targetHeight = 0;
    var test = contains ? doRatio > cRatio : doRatio < cRatio;
  
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