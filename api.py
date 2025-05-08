# api.py
from flask import Flask, request, jsonify
from flask_cors import CORS
import subprocess
import json

app = Flask(__name__)
CORS(app)  # Habilita CORS para todas as rotas

@app.route('/calculate', methods=['POST'])
def calculate():
    data = request.json
    cmd = ["python3", "run.py", "--json",
           str(data['daily_kwh']),
           str(data['price_kwh']),
           str(data['solar_gen']),
           str(data.get('solar_cost', 0))]
    
    result = subprocess.run(cmd, capture_output=True, text=True)
    return jsonify(json.loads(result.stdout))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)