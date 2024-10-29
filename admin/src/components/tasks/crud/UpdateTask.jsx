import React, { useEffect, useState } from "react";
import { Form, Button, Container, Alert } from "react-bootstrap";

const UpdateTask = ({onTaskUpdated}) => {
    const [tasks, setTasks] = useState([]);
    const [selectedTaskId, setSelectedTaskId] = useState('');
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const token = localStorage.getItem("token");

    // Récupérer toutes les tâches au chargement du composant
    useEffect(() => {
        const fetchTasks = async () => {
            try {
                const response = await fetch('http://localhost:8000/tasks', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                setTasks(data);
            } catch (err) {
                setError(err.message);
            }
        };

        fetchTasks();
    }, [token]);

    // Mettre à jour la tâche
    const handleUpdateTask = async (e) => {
        e.preventDefault(); // Empêcher le rechargement de la page
        try {
            const response = await fetch(`http://localhost:8000/tasks/${selectedTaskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ nom: name, description }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            setSuccess('Task successfully updated');
            setError(''); // Réinitialiser les erreurs
            onTaskUpdated(); // Notifier le parent pour rafraîchir les tâches
        } catch (err) {
            setError(err.message);
            setSuccess(''); // Réinitialiser le succès
        }
    };

    // Remplir les champs de nom et description lors de la sélection d'une tâche
    const handleTaskChange = (e) => {
        const taskId = e.target.value;
        setSelectedTaskId(taskId);

        const selectedTask = tasks.find((task) => task.task_id === parseInt(taskId));
        if (selectedTask) {
            setName(selectedTask.nom);
            setDescription(selectedTask.description);
        } else {
            setName('');
            setDescription('');
        }
    };

    return (
        <Container className="mt-5">
            <h2 className="mb-4">Update Task</h2>
            <Form onSubmit={handleUpdateTask}>
                <Form.Group controlId="taskSelect">
                    <Form.Label>Select Task:</Form.Label>
                    <Form.Control 
                        as="select" 
                        value={selectedTaskId} 
                        onChange={handleTaskChange} 
                        required
                    >
                        <option value="">Select a task</option>
                        {tasks.map((task) => (
                            <option key={task.task_id} value={task.task_id}>
                                {task.nom}
                            </option>
                        ))}
                    </Form.Control>
                </Form.Group>

                <Form.Group controlId="taskName">
                    <Form.Label>Name:</Form.Label>
                    <Form.Control
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                    />
                </Form.Group>

                <Form.Group controlId="taskDescription">
                    <Form.Label>Description:</Form.Label>
                    <Form.Control
                        type="text"
                        value={description}
                        onChange={(e) => setDescription(e.target.value)}
                        required
                    />
                </Form.Group>

                <Button variant="primary" type="submit">
                    Update Task
                </Button>
            </Form>

            {error && <Alert variant="danger" className="mt-3">{error}</Alert>}
            {success && <Alert variant="success" className="mt-3">{success}</Alert>}
        </Container>
    );
};

export default UpdateTask;
