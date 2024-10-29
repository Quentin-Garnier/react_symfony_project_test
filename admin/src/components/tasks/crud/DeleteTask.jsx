import React from "react";
import { Button } from "react-bootstrap";

const DeleteTask = ({ taskId, onTaskDeleted }) => {
    const token = localStorage.getItem("token");

    const handleDelete = async () => {
        if (window.confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) {
            try {
                const response = await fetch(`http://localhost:8000/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                if (response.ok) {
                    alert("Tâche supprimée avec succès !");
                    onTaskDeleted(); // Notify parent component to refresh the task list
                } else {
                    const errorData = await response.json();
                    alert(errorData.error || 'Une erreur est survenue');
                }
            } catch (err) {
                alert("Erreur lors de la suppression de la tâche: " + err.message);
            }
        }
    };

    return (
        <Button variant="danger" onClick={handleDelete}>
            Supprimer
        </Button>
    );
};

export default DeleteTask;
