<?php
interface DatabaseInterface {
    public function query($sql, $params = []);
    public function fetchAll($sql, $params = []);
    public function fetchOne($sql, $params = []);
    public function execute($sql, $params = []);
    public function lastInsertId();
}
