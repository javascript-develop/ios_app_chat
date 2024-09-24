const axios = require('axios');

const API_KEY = 'MzhEM0MwMUE1NTA0NDZCNTY2NEVBMzdFN0NEQjM0NDBFRTdENzA2ODZDREIyRkU1ODZEMzI1Q0YzRjZCNzEyMjg5NzhEQTkxMDVGMzlCRjc4ODc3RTRDMDQyNTNGMEMz';

// Define the API endpoint and parameters
const endpoint = 'https://public.ep-online.nl/api/v4/PandEnergielabel/Adres';
const postcode = '1234AB'; 
const huisnummer = '10';   


console.log('API Key:', API_KEY);


axios.get(`${endpoint}?postcode=${postcode}&huisnummer=${huisnummer}`, {
  headers: {
    'Authorization': `Bearer ${API_KEY}`,  
    'x-api-version': '4', 
    'Accept': 'application/json'  
  }
})
.then(response => {
  // Successfully received response
  console.log('Response data:', response.data);
})
.catch(error => {
  // Log the error and status code for debugging
  if (error.response) {
    console.error('Request failed with status code:', error.response.status);
    console.error('Response data:', error.response.data);
  } else {
    console.error('Error:', error.message);
  }
});
