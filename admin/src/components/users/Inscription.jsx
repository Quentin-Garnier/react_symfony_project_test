import React, { useState } from "react";
import { Form, Button, Container, Alert } from "react-bootstrap";
import { useNavigate } from "react-router-dom";
import { useEffect } from "react";

const InscriptionForm = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [prenom, setPrenom] = useState('');
    const [nom, setNom] = useState('');
    const [error, setError] = useState(null);

    const navigate = useNavigate();

    useEffect(() => {
        // Vérifier si l'utilisateur est authentifié et est administrateur
        const token = localStorage.getItem("token");
        const user = token ? JSON.parse(localStorage.getItem("user")) : null;

        if (!user || user.is_admin !== 1) {
            // Rediriger vers la page de connexion ou une autre page si l'utilisateur n'est pas admin
            navigate("/connection");
        }
    }, [navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await fetch('http://localhost:8000/inscription', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({prenom, nom, email, password }), // Envoie les données sous forme JSON
            });

            const data = await response.json();
            console.log(data);

            if (response.ok) {
                setError(null);
            } else {
                setError(data.error || 'Une erreur est survenue');
            }
        } catch (error) {
            setError('Erreur de connexion au serveur');
        }
    };

    return (
        <Container className="mt-5" style={{ maxWidth: '500px' }}>
            <h2 className="text-center mb-4">Inscription</h2>
            <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3" controlId="prenom">
                    <Form.Label>Prénom</Form.Label>
                    <Form.Control
                        type="text"
                        value={prenom}
                        onChange={(e) => setPrenom(e.target.value)}
                        required
                    />
                </Form.Group>

                <Form.Group className="mb-3" controlId="nom">
                    <Form.Label>Nom</Form.Label>
                    <Form.Control
                        type="text"
                        value={nom}
                        onChange={(e) => setNom(e.target.value)}
                        required
                    />
                </Form.Group>

                <Form.Group className="mb-3" controlId="email">
                    <Form.Label>Email</Form.Label>
                    <Form.Control
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />
                </Form.Group>

                <Form.Group className="mb-4" controlId="password">
                    <Form.Label>Mot de passe</Form.Label>
                    <Form.Control
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />
                </Form.Group>

                <Button variant="primary" type="submit" className="w-100">
                    Inscription
                </Button>
            </Form>

            {error && (
                <Alert variant="danger" className="mt-4">
                    {error}
                </Alert>
            )}
        </Container>
    );
}

export default InscriptionForm;
