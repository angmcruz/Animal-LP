
const map = L.map('map').setView([-1.8312, -78.1834], 6); 

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// L.marker([-0.1807, -78.4678]).addTo(map).bindPopup('Quito, Ecuador');
L.control.scale().addTo(map);
const animalesP = L.layerGroup().addTo(map);
fetch('animales.php')
  .then(r => r.json())
  .then(({ ok, animales }) => {
    if (!ok) return;
    const lista = [];
    animales.forEach(a => {
      const lat = Number(a.lat);
      const lng = Number(a.lng);
      if (lat && lng) {
        const marker = L.marker([lat, lng]).addTo(animalesP).bindPopup(`
            <strong>${a.nombre}</strong><br>
            Tipo: ${a.tipo}<br>
            Ecosistema: ${a.ecosistema}<br>
            ${a.foto ? `<img src="imagenes/${a.foto}" width="100">` : ''}
          `);
      }
    });
  })
  .catch(console.error);

