const API_KEY="AIzaSyBjiVyQzKD4GNR2Pq9gbo5EwY0mpqRxbVo";

async function geocodeAdresse(adresse) {
  const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(adresse)}&key=${API_KEY}`;

  const rep = await fetch(url);
  const data = await rep.json();

  if (data.status === "OK") {
    const result = data.results[0];
    const { lat, lng } = result.geometry.location;

    console.log("Adresse formatée :", result.formatted_address);
    console.log("Latitude :", lat);
    console.log("Longitude :", lng);

    return { lat, lng };
  } else {
    throw new Error(`Erreur Geocoding : ${data.status}`);
  }
}

