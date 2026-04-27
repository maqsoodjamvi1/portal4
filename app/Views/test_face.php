<!DOCTYPE html>
<html>
<head>
    <title>Face API Test</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.20.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body>
    <h2>Face API Model Test</h2>
    <div id="status">Testing...</div>
    
    <script>
    async function testModels() {
        const statusDiv = document.getElementById('status');
        const MODEL_URL = '/assets/face-api-models/';
        
        statusDiv.innerHTML = 'Checking if model files are accessible...<br>';
        
        // Test manifest file access
        try {
            const testResponse = await fetch(MODEL_URL + 'ssd_mobilenetv1_manifest.json');
            if (testResponse.ok) {
                statusDiv.innerHTML += '? Manifest file accessible<br>';
            } else {
                statusDiv.innerHTML += '? Manifest file NOT accessible (Status: ' + testResponse.status + ')<br>';
            }
        } catch(e) {
            statusDiv.innerHTML += '? Cannot access model files: ' + e.message + '<br>';
        }
        
        // Try loading models
        try {
            statusDiv.innerHTML += '<br>Loading SSD Mobilenet...<br>';
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
            statusDiv.innerHTML += '? SSD Mobilenet loaded<br>';
            
            statusDiv.innerHTML += 'Loading Face Landmark...<br>';
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            statusDiv.innerHTML += '? Face Landmark loaded<br>';
            
            statusDiv.innerHTML += 'Loading Face Recognition...<br>';
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            statusDiv.innerHTML += '? Face Recognition loaded<br>';
            
            statusDiv.innerHTML += '<br><span style="color:green; font-size:18px;">? ALL MODELS LOADED SUCCESSFULLY!</span>';
            
        } catch (error) {
            statusDiv.innerHTML += '<br><span style="color:red">? Error: ' + error.message + '</span>';
            console.error(error);
        }
    }
    
    testModels();
    </script>
</body>
</html>