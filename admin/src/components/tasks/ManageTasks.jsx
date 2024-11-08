import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import CreateTaskForm from "./crud/CreateTaskForm";
import UpdateTask from "./crud/UpdateTask";

const ManageTasks = () => {
    const navigate = useNavigate();
    const token = localStorage.getItem("token");
    const user = token ? JSON.parse(localStorage.getItem("user")) : null;
    const isAdmin = user && user.is_admin === 1;

    useEffect(() => {
        if (!isAdmin) {
            navigate("/tasks"); // Redirige vers GetTasks si l'utilisateur n'est pas un admin
        }
    }, [isAdmin, navigate]);

    const handleRefresh = () => {
        // Logique pour rafraîchir les tâches après la création ou la modification
    };

    return isAdmin ? (
        <div>
            <h1>Manage Tasks</h1>
            <UpdateTask onTaskUpdated={handleRefresh} />
            <CreateTaskForm onTaskCreated={handleRefresh} />
        </div>
    ) : null;
};

export default ManageTasks;
