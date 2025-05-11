<?php
class Assignment {
    // Database connection and table name
    private $conn;
    private $table_name = "assignments";

    // Object properties
    public $id;
    public $title;
    public $description;
    public $course_id;
    public $due_date;
    public $status;
    public $created_by;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create assignment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    title = :title,
                    description = :description,
                    course_id = :course_id,
                    due_date = :due_date,
                    created_at = :created_at,
                    updated_at = :updated_at";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
        $this->updated_at = htmlspecialchars(strip_tags($this->updated_at));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":updated_at", $this->updated_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update assignment
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    title = :title,
                    description = :description,
                    course_id = :course_id,
                    due_date = :due_date,
                    updated_at = :updated_at
                WHERE
                    id = :id";
        $stmt = $this->conn->prepare($query);
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        $this->updated_at = date('Y-m-d H:i:s');
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Delete assignment
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    // Publish assignment
    public function publish() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    status = 'published',
                    updated_at = :updated_at
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);
        $this->updated_at = date('Y-m-d H:i:s');
        $stmt->bindParam(":updated_at", $this->updated_at);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
} 