
const map = L.map('map').setView([-1.8312, -78.1834], 6); 

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

L.control.scale().addTo(map);
L.marker([-0.1807, -78.4678]).addTo(map)
  .bindPopup('Quito, Ecuador')
  .openPopup();




  