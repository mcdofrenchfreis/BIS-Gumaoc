import os
import subprocess
from datetime import datetime
from django.conf import settings
import logging

logger = logging.getLogger(__name__)


class BackupUtil:
    BACKUP_DIR = os.path.join(settings.BASE_DIR, 'backups')
    
    @classmethod
    def ensure_backup_dir(cls):
        if not os.path.exists(cls.BACKUP_DIR):
            os.makedirs(cls.BACKUP_DIR, exist_ok=True)
    
    @classmethod
    def list_backups(cls):
        cls.ensure_backup_dir()
        backups = []
        for file in os.listdir(cls.BACKUP_DIR):
            if file.endswith('.sql'):
                file_path = os.path.join(cls.BACKUP_DIR, file)
                backups.append({
                    'name': file,
                    'path': file_path,
                    'size': os.path.getsize(file_path),
                    'mtime': datetime.fromtimestamp(os.path.getmtime(file_path))
                })
        return sorted(backups, key=lambda x: x['mtime'], reverse=True)
    
    @classmethod
    def delete_backup(cls, filename):
        cls.ensure_backup_dir()
        file_path = os.path.join(cls.BACKUP_DIR, filename)
        if os.path.exists(file_path) and filename.endswith('.sql'):
            os.remove(file_path)
            return True
        return False
    
    @classmethod
    def export_database(cls, prefix='backup_'):
        cls.ensure_backup_dir()
        db_config = settings.DATABASES['default']
        db_name = db_config['NAME']
        db_user = db_config['USER']
        db_pass = db_config['PASSWORD']
        db_host = db_config['HOST']
        db_port = db_config.get('PORT', '3306')
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"{prefix}{db_name}_{timestamp}.sql"
        file_path = os.path.join(cls.BACKUP_DIR, filename)
        
        try:
            cmd = [
                'mysqldump',
                f'-h{db_host}',
                f'-P{db_port}',
                f'-u{db_user}',
                f'-p{db_pass}',
                db_name,
                '--single-transaction',
                '--routines',
                '--triggers'
            ]
            
            with open(file_path, 'w') as f:
                subprocess.run(cmd, stdout=f, check=True, text=True)
            
            logger.info(f"Database backup created: {filename}")
            return {
                'success': True,
                'file': filename,
                'path': file_path,
                'timestamp': timestamp
            }
        except subprocess.CalledProcessError as e:
            logger.error(f"Database backup failed: {str(e)}")
            return {
                'success': False,
                'error': str(e)
            }
        except Exception as e:
            logger.error(f"Database backup error: {str(e)}")
            return {
                'success': False,
                'error': str(e)
            }
    
    @classmethod
    def import_database(cls, filename):
        cls.ensure_backup_dir()
        file_path = os.path.join(cls.BACKUP_DIR, filename)
        
        if not os.path.exists(file_path):
            return {
                'success': False,
                'error': 'Backup file not found'
            }
        
        db_config = settings.DATABASES['default']
        db_name = db_config['NAME']
        db_user = db_config['USER']
        db_pass = db_config['PASSWORD']
        db_host = db_config['HOST']
        db_port = db_config.get('PORT', '3306')
        
        try:
            cmd = [
                'mysql',
                f'-h{db_host}',
                f'-P{db_port}',
                f'-u{db_user}',
                f'-p{db_pass}',
                db_name
            ]
            
            with open(file_path, 'r') as f:
                subprocess.run(cmd, stdin=f, check=True, text=True)
            
            logger.info(f"Database restored from: {filename}")
            return {
                'success': True,
                'file': filename
            }
        except subprocess.CalledProcessError as e:
            logger.error(f"Database restore failed: {str(e)}")
            return {
                'success': False,
                'error': str(e)
            }
        except Exception as e:
            logger.error(f"Database restore error: {str(e)}")
            return {
                'success': False,
                'error': str(e)
            }
    
    @classmethod
    def generate_readable_backup(cls):
        cls.ensure_backup_dir()
        db_config = settings.DATABASES['default']
        db_name = db_config['NAME']
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"readable_{db_name}_{timestamp}.txt"
        file_path = os.path.join(cls.BACKUP_DIR, filename)
        
        try:
            from django.db import connection
            cursor = connection.cursor()
            
            with open(file_path, 'w') as f:
                f.write(f"Barangay Database Readable Backup\n")
                f.write(f"Generated at: {datetime.now().isoformat()}\n")
                f.write(f"Database: {db_name}\n")
                f.write("=" * 60 + "\n\n")
                
                cursor.execute("SHOW TABLES")
                tables = cursor.fetchall()
                f.write(f"Tables found: {len(tables)}\n\n")
                
                for table in tables:
                    table_name = table[0]
                    cursor.execute(f"SELECT COUNT(*) FROM `{table_name}`")
                    count = cursor.fetchone()[0]
                    
                    f.write(f"- Table: {table_name}\n")
                    f.write(f"  Records: {count}\n")
                    
                    # Get last activity
                    cursor.execute(f"SHOW COLUMNS FROM `{table_name}`")
                    columns = [col[0] for col in cursor.fetchall()]
                    
                    last_activity = 'N/A'
                    for timestamp_col in ['updated_at', 'created_at', 'modified_at']:
                        if timestamp_col in columns:
                            cursor.execute(f"SELECT MAX(`{timestamp_col}`) FROM `{table_name}`")
                            result = cursor.fetchone()[0]
                            if result:
                                last_activity = str(result)
                                break
                    
                    f.write(f"  Last activity: {last_activity}\n\n")
            
            logger.info(f"Readable backup created: {filename}")
            return {
                'success': True,
                'file': filename,
                'path': file_path
            }
        except Exception as e:
            logger.error(f"Readable backup failed: {str(e)}")
            return {
                'success': False,
                'error': str(e)
            }
