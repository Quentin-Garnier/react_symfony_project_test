import React, { useState } from "react";
import { Form, Button, Container, Alert } from "react-bootstrap";

const CreateTaskForm = ({ onTaskCreated }) => {
    const [nom, setNom] = useState('');
    const [description, setDescription] = useState('');
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const token = localStorage.getItem("token");

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await fetch('http://localhost:8000/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ nom, description }),
            });

            if (response.ok) {
                const data = await response.json();
                setSuccess(data.success);
                setNom('');
                setDescription('');
                if (onTaskCreated) {
                    onTaskCreated(); // Notify the parent component to refresh tasks
                }
            } else {
                const errorData = await response.json();
                setError(errorData.error || 'Une erreur est survenue');
            }
        } catch (err) {
            setError('Erreur de connexion au serveur');
        }
    };

    return (
        <Container className="mt-4">
            <h2>Créer une nouvelle tâche</h2>
            {error && <Alert variant="danger">{error}</Alert>}
            {success && <Alert variant="success">{success}</Alert>}
            <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3" controlId="nom">
                    <Form.Label>Nom</Form.Label>
                    <Form.Control
                        type="text"
                        value={nom}
                        onChange={(e) => setNom(e.target.value)}
                        required
                    />
                </Form.Group>

                <Form.Group className="mb-3" controlId="description">
                    <Form.Label>Description</Form.Label>
                    <Form.Control
                        as="textarea"
                        rows={3}
                        value={description}
                        onChange={(e) => setDescription(e.target.value)}
                        required
                    />
                </Form.Group>

                <Button variant="primary" type="submit">
                    Créer
                </Button>
            </Form>
        </Container>
    );
};

export default CreateTaskForm;
