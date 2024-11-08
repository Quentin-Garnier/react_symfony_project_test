// App.js
import './App.css';
import LeadList from './components/LeadList';
import React from 'react';
import ConnectionForm from './components/users/ConnectonForm';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Header from './components/Header';
import InscriptionForm from './components/users/Inscription';
import GetTasks from './components/tasks/GetTasks';
import Response from './components/tasks/Response';
import Users from './components/Users';
import UserResponses from './components/UserResponses';
import ManageTasks from './components/tasks/ManageTasks';

function App() {
  return (
    <div className="App">
      <Router>
        <Header />
        <Routes>
          <Route path="/" element={<LeadList />} />
          <Route path="/connection" element={<ConnectionForm />} />
          <Route path="/inscription" element={<InscriptionForm />} />
          <Route path="/tasks" element={<GetTasks />} />
          <Route path="/response" element={<Response />} />
          <Route path="/responses/:id" element={<UserResponses />} />
          <Route path="/users" element={<Users />} />
          <Route path="/manageTasks" element={<ManageTasks />} />
          <Route path='*' element={<div>404</div>} />
        </Routes>
      </Router>
    </div>
  );
}

export default App;
