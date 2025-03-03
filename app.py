from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
import joblib

app = Flask(__name__)
CORS(app)  # Autoriser les requêtes depuis d'autres domaines

# Charger les données
df = pd.read_csv("data.csv")

# Vérifier les noms de colonnes et afficher les 5 premières lignes pour débogage
print("Columns in the dataset:", df.columns)
print(df.head())

# Vérifier si la colonne 'Dangerous' est présente
if 'Dangerous' not in df.columns:
    print("Column 'Dangerous' is missing!")
else:
    print("Column 'Dangerous' is present!")

# Identifier les colonnes catégorielles (pas la colonne 'Dangerous')
categorical_columns = df.select_dtypes(include=['object']).columns
categorical_columns = categorical_columns[categorical_columns != 'Dangerous']

# Appliquer one-hot encoding sur les autres colonnes catégorielles
df_encoded = pd.get_dummies(df, columns=categorical_columns, drop_first=True)

# Vérifier les premières lignes après encoding
print("Encoded DataFrame:")
print(df_encoded.head())

# Préparer les données pour l'entraînement
X = df_encoded.drop(['Dangerous'], axis=1)  # 'Dangerous' comme cible
y = df_encoded['Dangerous']

# Séparer les données en train/test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Entraîner un modèle simple
model = RandomForestClassifier()
model.fit(X_train, y_train)

# Sauvegarder le modèle
joblib.dump(model, "model.pkl")

# Charger le modèle une fois lors du démarrage de l'application
model = joblib.load("model.pkl")

@app.route("/predict", methods=["POST"])
def predict():
    try:
        data = request.get_json()

        # Vérifier que les données envoyées sont bien formatées
        if not data:
            return jsonify({"error": "No input data provided"}), 400

        # Convertir les données en DataFrame et appliquer one-hot encoding
        features = pd.DataFrame([data])

        # Appliquer le même encoding que pour les données d'entraînement
        features = pd.get_dummies(features, columns=categorical_columns, drop_first=True)

        # Assurer que les colonnes d'entrée correspondent à celles du modèle
        features = features.reindex(columns=X.columns, fill_value=0)

        # Prédiction
        prediction = model.predict(features)[0]

        return jsonify({"prediction": "Dangerous" if prediction == 1 else "Not Dangerous"})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)
