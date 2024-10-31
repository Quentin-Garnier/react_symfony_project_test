import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom"; // Importer useNavigate
import CreateTaskForm from "./crud/CreateTaskForm";
import { Button, Alert } from "react-bootstrap";
import DeleteTask from "./crud/DeleteTask"; // Gardez cela pour les admins
import UpdateTask from "./crud/UpdateTask";

const GetTasks = () => {
    const [error, setError] = useState(null);
    const [tasks, setTasks] = useState([]);
    const [users, setUsers] = useState([]);
    const [selectedUser, setSelectedUser] = useState({});
    const token = localStorage.getItem("token");
    const user = token ? JSON.parse(localStorage.getItem("user")) : null;
    const isAdmin = user && user.is_admin === 1; // Vérifie si l'utilisateur est un admin
    const userId = user ? user.user_id : null; // Récupérer l'ID de l'utilisateur
    const navigate = useNavigate(); // Utiliser useNavigate pour la redirection

    useEffect(() => {
        fetchTasks();
        fetchUsers();
    }, []);

    const fetchTasks = async () => {
        try {
            const response = await fetch('http://localhost:8000/tasks', {
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            setTasks(data);
        } catch (err) {
            setError(err.message);
        }
    };

    const fetchUsers = async () => {
        try {
            const response = await fetch('http://localhost:8000/users',{
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            setUsers(data);
        } catch (err) {
            setError(err.message);
        }
    };

    const handleUserChange = (task_id, user_id) => {
        setSelectedUser({ [task_id]: user_id });
    };

    const assignUserToTask = async (task_id) => {
        const user_id = selectedUser[task_id];
        if (!user_id) {
            alert("Veuillez sélectionner un utilisateur !");
            return;
        }
        try {
            const response = await fetch(`http://localhost:8000/tasks/${task_id}/assign`, {
                method: 'PUT',
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ user_id })
            });
            if (response.ok) {
                alert("Tâche assignée avec succès !");
            } else {
                setError(`Erreur lors de l'assignation de la tâche ${task_id}`);
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleRefresh = () => {
        fetchTasks(); // Rafraîchit la liste des tâches après la création ou la suppression d'une tâche
    };

    // Filtre les tâches selon l'utilisateur connecté
    const filteredTasks = isAdmin 
        ? tasks 
        : tasks.filter(task => task.assigned_users && task.assigned_users.includes(userId));

    const handleRespondClick = (task_id) => {
        localStorage.setItem("currentTaskId", task_id); // Stocke l'ID de la tâche dans le local storage
        navigate("/response"); // Redirige vers la page /response
    };

    return (
        <div className="container">
            <UpdateTask onTaskUpdated={handleRefresh} />
            <CreateTaskForm onTaskCreated={handleRefresh} />
            <h2 className="mt-4">Liste des tâches</h2>
            {error && <Alert variant="danger">{error}</Alert>}
            {filteredTasks.length > 0 ? (
                <table className="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Description</th>
                            {isAdmin && <th scope="col">Assigner à</th>} {/* Colonne conditionnelle */}
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredTasks.map((task) => (
                            <tr key={task.task_id}>
                                <td>{task.nom}</td>
                                <td>{task.description}</td>
                                {isAdmin && ( // Affiche la colonne "Assigner à" uniquement si l'utilisateur est un admin
                                    <td>
                                        <select
                                            className="form-select"
                                            value={selectedUser[task.task_id] || ""}
                                            onChange={(e) => handleUserChange(task.task_id, e.target.value)}
                                        >
                                            <option value="">Sélectionnez un utilisateur</option>
                                            {users.map((user) => (
                                                <option key={user.user_id} value={user.user_id}>
                                                    {user.nom} {user.prenom} ({user.user_id})
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                )}
                                <td>
                                    {isAdmin ? ( // Affiche le bouton de suppression si l'utilisateur est un admin
                                        <DeleteTask taskId={task.task_id} onTaskDeleted={handleRefresh} />
                                    ) : ( // Affiche le bouton "Répondre" si l'utilisateur n'est pas un admin
                                        <Button
                                            variant="secondary"
                                            onClick={() => handleRespondClick(task.task_id)} // Gère le clic sur le bouton Répondre
                                        >
                                            Répondre
                                        </Button>
                                    )}
                                    {isAdmin && ( // Affiche le bouton d'assignation uniquement si l'utilisateur est un admin
                                        <Button
                                            variant="primary"
                                            onClick={() => assignUserToTask(task.task_id)}
                                        >
                                            Assigner
                                        </Button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            ) : (
                <p>Aucune tâche trouvée</p>
            )}
        </div>
    );
};

export default GetTasks;
