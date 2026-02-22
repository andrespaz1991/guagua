import mysql.connector
import os
import glob
import subprocess
import time

def connect_db():
    try:
        conn = mysql.connector.connect(
            host="srv765.hstgr.io",
            user="u417538463_root",
            password="Celeste2025.",
            database="u417538463_guagua"
        )
        return conn
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None

def drop_all_tables(conn):
    cursor = conn.cursor()
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
    cursor.execute("SHOW TABLES;")
    tables = cursor.fetchall()
    
    for table in tables:
        cursor.execute(f"DROP TABLE `{table[0]}`;")
        print(f"Tabla {table[0]} eliminada.")
    
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
    cursor.close()

def get_latest_sql_file(directory):
    list_of_files = glob.glob(os.path.join(directory, "*.sql"))  # Busca todos los archivos .sql en el directorio
    if not list_of_files:
        print("No se encontraron archivos .sql en el directorio.")
        return None
    latest_file = max(list_of_files, key=os.path.getmtime)  # Obtiene el archivo más reciente por fecha y hora de modificación
    return latest_file

def is_file_empty(file_path):
    return os.path.exists(file_path) and os.path.getsize(file_path) == 0

def estimate_import_time(file_size):
    avg_speed = 1024 * 50  # 50 KB/s estimado (ajustable según rendimiento)
    return max(1, file_size // avg_speed)

def import_database(sql_file):
    if not sql_file:
        print("No se encontró un archivo SQL para importar.")
        return
    
    if is_file_empty(sql_file):
        print(f"El archivo {sql_file} está vacío. No se puede importar.")
        return
    
    file_size = os.path.getsize(sql_file)
    estimated_time = estimate_import_time(file_size)
    print(f"Importando base de datos desde: {sql_file} ({file_size / 1024:.2f} KB) - Tiempo estimado: {estimated_time} segundos")
    
    mysql_path = "D:\\xampp\\mysql\\bin\\mysql"
    command = f"{mysql_path} -h srv765.hstgr.io -u u417538463_root -pCeleste2025. u417538463_guagua"
    
    try:
        start_time = time.time()
        with open(sql_file, "r", encoding="utf-8") as sql_dump:
            process = subprocess.Popen(command, stdin=sql_dump, shell=True)
            
            for remaining in range(estimated_time, 0, -1):
                elapsed_time = time.time() - start_time
                progress = ((estimated_time - remaining) / estimated_time) * 100
                print(f"Progreso: {progress:.2f}% - Tiempo restante: {remaining} segundos", end="\r")
                time.sleep(1)
        
        end_time = time.time()
        total_time = end_time - start_time
        print(f"\nBase de datos importada correctamente desde: {sql_file}")
        print(f"Tiempo total de importación: {total_time:.2f} segundos")
    except subprocess.CalledProcessError as e:
        print(f"Error al importar la base de datos: {e}")

def main():
    conn = connect_db()
    if conn:
        drop_all_tables(conn)
        conn.close()
        print("Tablas eliminadas correctamente.")
        
        sql_directory = "D:\\xampp\\htdocs\\guagua\\reportes\\bd\\automatico"
        sql_file = get_latest_sql_file(sql_directory)
        import_database(sql_file)
    else:
        print("No se pudo conectar a la base de datos.")

if __name__ == "__main__":
    main()