import React, { useState } from "react";
import { Button, Container, Alert } from "react-bootstrap";

const UploadResponse = () => {
    const [file, setFile] = useState(null);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    const handleFileChange = (e) => {
        setFile(e.target.files[0]);
        setError(null);
        setSuccess(null);
    };

    const handleUpload = async () => {
        if (!file) {
            setError("Veuillez sélectionner un fichier CSV.");
            return;
        }

        const reader = new FileReader();
        reader.onload = async (event) => {
            const text = event.target.result;
            const tasks = parseCSV(text);
            
            if (tasks) { // Continue seulement si le fichier est valide
                await createTasks(tasks);
            }
        };
        reader.onerror = () => {
            setError("Erreur lors de la lecture du fichier.");
        };

        reader.readAsText(file);
    };

    const parseCSV = (text) => {
        const rows = text.split("\n").filter((row) => row.trim() !== ""); // Sépare par lignes
        if (rows.length !== 2) { // Vérifie que le fichier ne contient qu'une en-tête et une seule ligne de contenu
            setError("Le fichier CSV doit contenir uniquement une ligne d'en-tête et une seule ligne de contenu.");
            return null; // Arrête le traitement si le format n'est pas valide
        }

        const [header, content] = rows;
        if (header !== "nom,description") { // Vérifie que l'en-tête est correcte
            setError("Le fichier CSV doit contenir l'en-tête 'nom,description'.");
            return null;
        }
        const [nom, description] = content.split(",").map(item => item.trim());
        
        // Vérifie que les champs requis sont présents
        if (!nom || !description) {
            setError("Le fichier CSV doit contenir les colonnes 'nom' et 'description' correctement remplies.");
            return null;
        }

        return [{ nom, description }]; // Renvoie un tableau avec un seul objet tâche
    };

    const createTasks = async (tasks) => {
        const token = localStorage.getItem("token");
        const responding_user = JSON.parse(localStorage.getItem("user")).user_id;
        const assigned_task = localStorage.getItem("currentTaskId");

        try {
            const responses = await Promise.all(tasks.map(async (task) => {

                const taskWithAdditionalFields = {
                    ...task,
                    assigned_task: assigned_task,
                    responding_user: responding_user,
                };


                const response = await fetch('http://localhost:8000/response', {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify(taskWithAdditionalFields),
                });
                return response.ok ? response.json() : Promise.reject(await response.json());
            }));

            setSuccess("Réponse envoyée avec succès !");
            console.log("Réponse envoyée :", responses);
        } catch (err) {
            setError(err.error || "Erreur lors de l'envoi de la réponse'.");
            console.error(err);
        }
    };

    return (
        <Container className="mt-5" style={{ maxWidth: '500px' }}>
            <h2 className="text-center mb-4">Charger une réponse</h2>
            <input
                type="file"
                accept=".csv"
                onChange={handleFileChange}
            />
            <Button
                variant="primary"
                onClick={handleUpload}
                className="mt-3"
            >
                Charger
            </Button>
            {error && (
                <Alert variant="danger" className="mt-4">
                    {error}
                </Alert>
            )}
            {success && (
                <Alert variant="success" className="mt-4">
                    {success}
                </Alert>
            )}
        </Container>
    );
};

export default UploadResponse;
