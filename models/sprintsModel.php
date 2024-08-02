<?php
class SprintModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllSprints() {
        $query = "SELECT s.id, s.nombre, 
                         j1.nombre as juego1, j2.nombre as juego2, j3.nombre as juego3, 
                         j4.nombre as juego4, j5.nombre as juego5, j6.nombre as juego6
                  FROM sprint s
                  LEFT JOIN juegos j1 ON s.juego1 = j1.id
                  LEFT JOIN juegos j2 ON s.juego2 = j2.id
                  LEFT JOIN juegos j3 ON s.juego3 = j3.id
                  LEFT JOIN juegos j4 ON s.juego4 = j4.id
                  LEFT JOIN juegos j5 ON s.juego5 = j5.id
                  LEFT JOIN juegos j6 ON s.juego6 = j6.id";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getSprint($id) {
        $query = "SELECT * FROM sprint WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createSprint($data) {
        $query = "INSERT INTO sprint (nombre, juego1, juego2, juego3, juego4, juego5, juego6) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", $data['nombre'], $data['juego1'], $data['juego2'], 
                          $data['juego3'], $data['juego4'], $data['juego5'], $data['juego6']);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function updateSprint($id, $data) {
        $query = "UPDATE sprint SET nombre = ?, juego1 = ?, juego2 = ?, 
                  juego3 = ?, juego4 = ?, juego5 = ?, juego6 = ? 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssssi", $data['nombre'], $data['juego1'], $data['juego2'], 
                          $data['juego3'], $data['juego4'], $data['juego5'], $data['juego6'], $id);
        return $stmt->execute();
    }

    public function deleteSprint($id) {
        $query = "DELETE FROM sprint WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAllJuegos() {
        $query = "SELECT id, nombre FROM juegos";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}