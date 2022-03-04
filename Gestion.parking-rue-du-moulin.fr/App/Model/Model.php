<?php
namespace App\Model;

use App\Config\DataBase;

use PDO;

abstract class Model{

    protected $connexion;
    protected string $table;

    public function __construct()
    {
        $this->connexion = (new DataBase ())->getConnection();
    }

    public function GetAll(string $join = "", string $order= "") : array {
        $requete = $this->connexion->prepare("SELECT * FROM " . $this->table . " " . $join . " " . $order);
        $requete->execute();
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetOne(int $id, string $join = "", string $order= "") : array {
        $requete = $this->connexion->prepare("SELECT * FROM " . $this->table . " " . $join . " WHERE id = :id" . " " . $order);
        $requete->BindParam(":id",$id);
        $requete->execute();
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    public function FindAll(string $champs, array $data, string $join = "", string $order= "") : array{

        $sql= "";

        foreach($data as $cle => $valeur){
            $sql = $sql . $cle . " = " . "'" . $valeur . "'" . " AND ";
        }

        $sql = substr($sql,0,strlen($sql)-4);

        $requete = $this->connexion->prepare("SELECT " . $champs . " FROM " . $this->table . " " . $join . " WHERE " . $sql . " " . $order);
        $requete->execute();
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function FindOne(string $champs, array $data, string $join = "", string $order= "") : array{

        $sql= "";

        foreach($data as $cle => $valeur){
            $sql = $sql . $cle . " = " . "'" . $valeur . "'" . " AND ";
        }

        $sql = substr($sql,0,strlen($sql)-4);

        $requete = $this->connexion->prepare("SELECT " . $champs . " FROM " . $this->table . " WHERE " . $sql . " " . $order);
        $requete->execute();
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    public function Update(array $data, array $find) : int{

        $sql_find = "";
        $sql_set = "";

        foreach($find as $cle => $valeur){
            $sql_find = $sql_find . $cle . " = " . "'" . $valeur . "'" . " AND ";
        }

        foreach($data as $cle => $valeur){
            $sql_set = $sql_set . $cle . " = " . "'" . $valeur . "'" . ", ";
        }

        $sql_find = substr($sql_find,0,strlen($sql_find)-4);
        $sql_set = substr($sql_set,0,strlen($sql_set)-2);

        $requete = $this->connexion->prepare("UPDATE " . $this->table . " SET " . $sql_set . " WHERE " . $sql_find . " ");
        $requete->execute();
        return $requete->rowCount();
    }

    public function Insert(array $data) : int{

        $sql_debut = "";
        $sql_fin = "";

        foreach($data as $cle => $valeur){
            $sql_debut = $sql_debut . $cle . " , ";
            $sql_fin = $sql_fin . "'" . $valeur . "',";
        }

        $sql_debut = substr($sql_debut,0,strlen($sql_debut)-2);
        $sql_fin = substr($sql_fin,0,strlen($sql_fin)-1);

        $requete = $this->connexion->prepare("INSERT INTO " .$this->table . " (" . $sql_debut . ") VALUES (" . $sql_fin . ") ");
        $requete->execute();
        return $requete->rowCount();
    }

    public function Delete(array $data) : int{

        $sql_find = "";

        foreach($data as $cle => $valeur){
            $sql_find = $sql_find . $cle . " = " . "'" . $valeur . "'" . " AND ";
        }

        $sql_find = substr($sql_find,0,strlen($sql_find)-4);

        $requete = $this->connexion->prepare("DELETE FROM " .$this->table . " WHERE " . $sql_find . " ");
        $requete->execute();
        return $requete->rowCount();
    }

}
