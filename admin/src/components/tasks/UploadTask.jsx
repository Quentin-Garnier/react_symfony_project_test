import React, { useState } from "react";
import { Button, Container, Alert } from "react-bootstrap";

const UploadTask = () => {
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
            await createTasks(tasks);
        };
        reader.onerror = () => {
            setError("Erreur lors de la lecture du fichier.");
        };

        reader.readAsText(file);
    };

    const parseCSV = (text) => {
        const rows = text.split("\n").filter((row) => row.trim() !== ""); // Sépare par lignes
        const tasks = rows.slice(1).map((row) => { // Ignore la première ligne
            const [nom, description] = row.split(",").map(item => item.trim());
            return { nom, description }; // Renvoie un objet tâche
        });
        return tasks;
    };

    const createTasks = async (tasks) => {
        const token = localStorage.getItem("token");

        try {
            const responses = await Promise.all(tasks.map(async (task) => {
                const response = await fetch('http://localhost:8000/tasks', {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify(task),
                });
                return response.ok ? response.json() : Promise.reject(await response.json());
            }));

            setSuccess("Tâches créées avec succès !");
            console.log("Tâches créées :", responses);
        } catch (err) {
            setError(err.error || "Erreur lors de la création des tâches.");
            console.error(err);
        }
    };

    return (
        <Container className="mt-5" style={{ maxWidth: '500px' }}>
            <h2 className="text-center mb-4">Charger des Tâches</h2>
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

export default UploadTask;
