<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/sql.php';

/** Accesos del menú compartido. No instala aplicaciones ni ejecuta sus archivos. */
final class GuaguaMenuRepository
{
    private const COLUMNS = 'menu_item_id, menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav, is_cabecera';

    public function __construct(private mysqli $db) {}

    public function items(bool $lock = false): array
    {
        $result = $this->db->query('SELECT ' . self::COLUMNS . ' FROM menu_items2 ORDER BY menu_item_id' . ($lock ? ' FOR UPDATE' : ''));
        if (!$result) throw new RuntimeException('No se pudieron consultar los accesos del menú.');
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['menu_item_id'] = (int)$row['menu_item_id'];
            $row['menu_parent_id'] = $row['menu_parent_id'] ? (int)$row['menu_parent_id'] : null;
            $row['fav'] = (int)$row['fav'] === 1;
            $row['is_cabecera'] = (int)$row['is_cabecera'] === 1;
        }
        unset($row);
        return $rows;
    }

    public function icons(): array
    {
        try {
            $result = $this->db->query('SELECT icono, imagen_icono FROM iconos ORDER BY icono');
            if (!$result) return [];
            $rows = $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $error) { return []; }
        $icons = [];
        foreach ($rows as $row) {
            $file = basename(str_replace('\\', '/', (string)$row['imagen_icono']));
            $slug = pathinfo($file, PATHINFO_FILENAME);
            if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $slug) || !preg_match('/\.(?:png|jpe?g|gif|webp)$/i', $file)) continue;
            $icons[] = ['nombre'=>(string)$row['icono'], 'valor'=>'icon-sga-' . $slug, 'imagen'=>$file];
        }
        return $icons;
    }

    private static function text(array $input, string $key, int $limit, bool $required = false): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value) || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) throw new InvalidArgumentException('Hay un campo de texto no válido.');
        $value = trim($value);
        if (($required && $value === '') || mb_strlen($value, 'UTF-8') > $limit) throw new InvalidArgumentException('Revisa el campo ' . $key . ' y su longitud máxima.');
        return $value;
    }

    private static function flag($value): bool
    {
        if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) throw new InvalidArgumentException('El estado no es válido.');
        return (bool)$value;
    }

    private static function id($value, bool $optional = false): ?int
    {
        if ($optional && in_array($value, [null, '', 0, '0'], true)) return null;
        if (!is_int($value) && !is_string($value)) throw new InvalidArgumentException('El identificador no es válido.');
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>2147483647]]);
        if ($id === false) throw new InvalidArgumentException('El identificador no es válido.');
        return $id;
    }

    public static function validate(array $input): array
    {
        $item = [
            'menu_item_name'=>self::text($input, 'menu_item_name', 255, true),
            'menu_description'=>self::text($input, 'menu_description', 10000),
            'menu_url'=>self::text($input, 'menu_url', 255, true),
            'categoria'=>self::text($input, 'categoria', 255),
            'icono'=>self::text($input, 'icono', 25),
            'url_target'=>$input['url_target'] ?? '_self',
            'menu_parent_id'=>self::id($input['menu_parent_id'] ?? null, true),
            'fav'=>self::flag($input['fav'] ?? false),
            'is_cabecera'=>self::flag($input['is_cabecera'] ?? false),
        ];
        $url = str_replace(' ', '%20', $item['menu_url']);
        if (strlen($url) > 255) throw new InvalidArgumentException('El enlace supera la longitud máxima.');
        $item['menu_url'] = $url;
        $parts = parse_url($url);
        if ($parts === false || preg_match('/[\x00-\x20\\\\<>"\']/', $url)
            || (!empty($parts['scheme']) && !in_array(strtolower($parts['scheme']), ['http', 'https'], true))
            || isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('Usa una ruta de Guagua o un enlace http/https válido, sin espacios.');
        }
        if (!in_array($item['url_target'], ['_self', '_blank'], true)) throw new InvalidArgumentException('El destino de la pestaña no es válido.');
        if ($item['is_cabecera']) $item['fav'] = true;
        return $item;
    }

    private function get(int $id): array
    {
        $row = guagua_sql($this->db, 'SELECT ' . self::COLUMNS . ' FROM menu_items2 WHERE menu_item_id = ? FOR UPDATE', [$id], 'i')->fetch_assoc();
        if (!$row) throw new RuntimeException('Este acceso ya no existe. Actualiza la lista.');
        return $row;
    }

    private function transaction(callable $action)
    {
        if (!$this->db->begin_transaction()) throw new RuntimeException('No se pudo iniciar la actualización del menú.');
        try { $result = $action(); if (!$this->db->commit()) throw new RuntimeException('No se pudo guardar la actualización del menú.'); return $result; }
        catch (Throwable $error) { $this->db->rollback(); throw $error; }
    }

    public function save(array $input, $id = null): int
    {
        $item = self::validate($input);
        $id = self::id($id, true);
        return $this->transaction(function () use ($item, $id) {
            $rows = $this->items(true);
            if ($id !== null) $this->get($id);
            $parents = array_column($rows, 'menu_parent_id', 'menu_item_id');
            $parent = $item['menu_parent_id']; $seen = [];
            while ($parent !== null) {
                if ($parent === $id || isset($seen[$parent])) throw new RuntimeException('Un acceso no puede depender de sí mismo ni formar un ciclo.');
                if (!array_key_exists($parent, $parents)) throw new RuntimeException('El acceso superior ya no existe.');
                $seen[$parent] = true; $parent = $parents[$parent];
            }
            $values = [$item['menu_item_name'], $item['menu_description'], $item['menu_url'], $item['menu_parent_id'], $item['url_target'], $item['categoria'], $item['icono'], (int)$item['fav'], (int)$item['is_cabecera']];
            if ($id === null) {
                guagua_sql($this->db, 'INSERT INTO menu_items2 (menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav, is_cabecera) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $values, 'sssisssii');
                return (int)$this->db->insert_id;
            }
            $values[] = $id;
            guagua_sql($this->db, 'UPDATE menu_items2 SET menu_item_name=?, menu_description=?, menu_url=?, menu_parent_id=?, url_target=?, categoria=?, icono=?, fav=?, is_cabecera=? WHERE menu_item_id=?', $values, 'sssisssiii');
            return $id;
        });
    }

    public function setFlag($id, string $field, $value): void
    {
        $id = self::id($id); $value = self::flag($value);
        if (!in_array($field, ['fav', 'is_cabecera'], true)) throw new InvalidArgumentException('No se puede modificar ese estado.');
        $this->transaction(function () use ($id, $field, $value) {
            $this->get($id);
            if ($field === 'is_cabecera' && $value) {
                guagua_sql($this->db, 'UPDATE menu_items2 SET is_cabecera=1, fav=1 WHERE menu_item_id=?', [$id], 'i');
            } elseif ($field === 'fav' && !$value) {
                guagua_sql($this->db, 'UPDATE menu_items2 SET fav=0, is_cabecera=0 WHERE menu_item_id=?', [$id], 'i');
            } else {
                guagua_sql($this->db, 'UPDATE menu_items2 SET ' . $field . '=? WHERE menu_item_id=?', [(int)$value, $id], 'ii');
            }
        });
    }

    public function delete($id): void
    {
        $id = self::id($id);
        $this->transaction(function () use ($id) {
            $this->get($id);
            $children = guagua_sql($this->db, 'SELECT menu_item_id FROM menu_items2 WHERE menu_parent_id=? LIMIT 1 FOR UPDATE', [$id], 'i');
            if ($children->num_rows > 0) throw new RuntimeException('Este acceso tiene accesos dependientes. Cambia su acceso superior antes de quitarlo.');
            guagua_sql($this->db, 'DELETE FROM menu_items2 WHERE menu_item_id=?', [$id], 'i');
        });
    }

    public function sync(array $ids, GuaguaAppStore $store): int
    {
        if (!$ids || count($ids) > 200) throw new InvalidArgumentException('Selecciona entre 1 y 200 aplicaciones.');
        foreach ($ids as $id) if (!is_string($id) || !preg_match('/^[a-f0-9]{24}$/D', $id)) throw new InvalidArgumentException('La selección no es válida.');
        return $this->transaction(function () use ($ids, $store) {
            $apps = array_column($store->catalog($this->items(true)), null, 'id');
            $count = 0;
            foreach (array_unique($ids) as $id) {
                $app = $apps[$id] ?? null;
                if (!$app || $app['estado'] !== 'instalada') throw new RuntimeException('Una app seleccionada ya no está instalada. Actualiza la lista.');
                if (!empty($app['menu_id'])) continue;
                guagua_sql($this->db, "INSERT INTO menu_items2 (menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav, is_cabecera) VALUES (?, ?, ?, NULL, '_self', ?, ?, 0, 0)", [$app['nombre'], $app['descripcion'], $app['ruta'], $app['categoria'], $app['icono']]);
                $count++;
            }
            return $count;
        });
    }
}
