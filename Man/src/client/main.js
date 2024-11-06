import Arret from './Arret.js'



/** @type {HTMLCanvasElement} */
const busCanvas = document.getElementById("busCanvas");
const ctx = busCanvas.getContext("2d");
const sizeX = busCanvas.getAttribute("width");
const sizeY = busCanvas.getAttribute("heigth");
const oX = Math.round(sizeX/2);
const oY = Math.round(sizeY/2);
const canvasRect = busCanvas.getBoundingClientRect();

let mouseX = null;
let mouseY = null;


document.addEventListener("mousemove", (e) => {
  mouseX = e.clientX - canvasRect.left;
  mouseY = e.clientY - canvasRect.top;
  render();
})

let arrets = {};
let arretsSet = []; 

  // Événement déclenché lorsque des messages sont reçus
  socket.addEventListener('message', ()=>{ socket.addEventListener('message', (event)=>{ setup(event.data); }, {once: true}); }, {once: true});  

  function setup(string) {
    socket.send('pause');  
    let json = JSON.parse(string);        
    console.log(json);
    setArret(json);
    console.log(arrets); 
    setLinks(json); 
    setCoordinates();         
    }

  function setArret(json){
    for (let arret in json[0]["arrets"]){
        arrets[arret] = (new Arret(arret,ctx));
        arretsSet.push(false);
    }
  }

  function setLinks(json){
    let routes=json[4]["routes"]
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

  async function setCoordinates(){    
          addEventListener("click",function setter() {      
            let arretsList = Object.values(arrets);    
            let index = 0;                           
            if(index<arretsList.length){
            }
            else{
              render();
              //socket.addEventListener('message', (event) => { update(event.data) });
              socket.send('resume');
            }
          });

  }

function render() {
    Object.values(arrets).forEach((arret) => {
      arret.draw();
      arret.checkHover(mouseX, mouseY);
      arret.update();
    });
  }