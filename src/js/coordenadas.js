let mapReg, marker;
const EC = [-1.8312, -78.1834];

function setInputs(lat, lng) {
  document.getElementById('lat').value = lat.toFixed(6);
  document.getElementById('lng').value = lng.toFixed(6);
}

function putMarker(lat, lng) {
  if (marker) {
    marker.setLatLng([lat, lng]);
  } else {
    marker = L.marker([lat, lng], { draggable: true }).addTo(mapReg);
    marker.on('dragend', () => {
      const p = marker.getLatLng();
      setInputs(p.lat, p.lng);
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  
  mapReg = L.map('mapRegistro').setView(EC, 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(mapReg);

  putMarker(EC[0], EC[1]);
  setInputs(EC[0], EC[1]);

  
  mapReg.on('click', (e) => {
    putMarker(e.latlng.lat, e.latlng.lng);
    setInputs(e.latlng.lat, e.latlng.lng);
  });

  
  document.getElementById('btnGeo').onclick = () => {
    if (!navigator.geolocation) return alert('Geolocalización no soportada');
    navigator.geolocation.getCurrentPosition(pos => {
      const { latitude, longitude } = pos.coords;
      mapReg.setView([latitude, longitude], 15);
      putMarker(latitude, longitude);
      setInputs(latitude, longitude);
    }, () => alert('No se pudo obtener tu ubicación'));
  };
});
