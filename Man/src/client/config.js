function toggleAccordion(sectionId) {
    const section = document.getElementById(sectionId);
    section.classList.toggle("hidden-content");
}

// Ajout dynamique de champs pour chaque section avec paramètres
function addArret(nom = "", routes = "") {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            <label class="block text-gray-700 col-span-1">Nom de l'arrêt: <input type="text" list="arretsOptions" class="arretNom p-2 border rounded w-full" value="${nom}" required></label>
            <label class="block text-gray-700 col-span-1">Routes: <input type="text" list="routesOptions" class="arretRoutes p-2 border rounded w-full" value="${routes}" placeholder="route1, route2" required></label>
                      <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
        `;
    document.getElementById("arretsList").appendChild(div);
}

function addBus(
    type = "",
    capacite = "",
    vitesseChargement = "",
    vitesseDeplacement = ""
) {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-4 gap-4">
            <label class="block text-gray-700 col-span-1">Type: <input type="text" list="busTypesOptions" class="busType p-2 border rounded w-full" value="${type}" required></label>
            <label class="block text-gray-700 col-span-1">Capacité max: <input type="number" class="busCapacite p-2 border rounded w-full" value="${capacite}" required></label>
            <label class="block text-gray-700 col-span-1">Vitesse de chargement: <input type="number" class="busVitesseChargement p-2 border rounded w-full" value="${vitesseChargement}" required></label>
            <label class="block text-gray-700 col-span-1">Vitesse de déplacement: <input type="number" class="busVitesseDeplacement p-2 border rounded w-full" value="${vitesseDeplacement}" required></label>
                      <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
        `;
    document.getElementById("busList").appendChild(div);
}

function addRoute(nom = "", arrets = "", distance = "") {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            <label class="block text-gray-700 col-span-1">Nom de la route: <input type="text" list="routesOptions" class="routeNom p-2 border rounded w-full" value="${nom}" required></label>
            <label class="block text-gray-700 col-span-1">Arrêts: <input type="text" list="arretsOptions" class="routeArrets p-2 border rounded w-full" value="${arrets}" placeholder="A, B" required></label>
            <label class="block text-gray-700 col-span-1">Distance: <input type="number" class="routeDistance p-2 border rounded w-full" value="${distance}" required></label>
                      <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
        `;
    document.getElementById("routesList").appendChild(div);
}

// Buses
function addBuses(type = "", parcours = "") {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
    <label class="block text-gray-700 col-span-1">Type: <input type="text" class="busType p-2 border rounded w-full" value="${type}" required></label>
    <label class="block text-gray-700 col-span-1">Parcours: <input type="text" class="busParcours p-2 border rounded w-full" value="${parcours}" placeholder="parcours1" required></label>
              <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
`;
    document.getElementById("busesList").appendChild(div);
}

// Parcours
function addParcours(nom = "", etapes = "") {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
    <label class="block text-gray-700 col-span-1">Nom du parcours: <input type="text" class="parcoursNom p-2 border rounded w-full" value="${nom}" required></label>
    <label class="block text-gray-700 col-span-1">Étapes: <input type="text" class="parcoursEtapes p-2 border rounded w-full" value="${etapes}" placeholder="A, B, C" required></label>
              <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
`;
    document.getElementById("parcoursList").appendChild(div);
}

// Passagers
function addPerson(nom = "", nombre = "", aller = {}, retour = {}) {
    const div = document.createElement("div");
    div.classList.add("space-y-4");
    div.innerHTML = `
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-4">
        <label class="block text-gray-700 col-span-1">Nom du passager: <input type="text" class="personNom p-2 border rounded w-full" value="${nom}" required></label>
        <label class="block text-gray-700 col-span-1">Nombre: <input type="number" class="personNombre p-2 border rounded w-full" value="${nombre}" required></label>
        <br>
        <label class="block text-gray-700 col-span-1">Aller - Départ: <input type="text" class="personAllerDepart p-2 border rounded w-full" value="${aller.depart || ""
        }" required></label>
        <label class="block text-gray-700 col-span-1">Aller - Arrivée: <input type="text" class="personAllerArrivee p-2 border rounded w-full" value="${aller.arrivee || ""
        }" required></label>
        <label class="block text-gray-700 col-span-1">Aller - Temps: <input type="number" class="personAllerTemps p-2 border rounded w-full" value="${aller.temps || 0
        }" required></label>
        <label class="block text-gray-700 col-span-1">Retour - Départ: <input type="text" class="personRetourDepart p-2 border rounded w-full" value="${retour.depart || ""
        }" required></label>
        <label class="block text-gray-700 col-span-1">Retour - Arrivée: <input type="text" class="personRetourArrivee p-2 border rounded w-full" value="${retour.arrivee || ""
        }" required></label>
        <label class="block text-gray-700 col-span-1">Retour - Temps: <input type="number" class="personRetourTemps p-2 border rounded w-full" value="${retour.temps || 0
        }" required></label>
      <div class="col-span-2 sm:col-span-2 lg:col-span-3 flex justify-center mt-4">
        <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="this.parentElement.parentElement.remove()">Supprimer</button>
      </div>
    </div>
    `;
    document.getElementById("peoplesList").appendChild(div);
}