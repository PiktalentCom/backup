Backup your projects and upload on Dropbox 

Database supported for the moment ```postgresql```

```yml
instances:
    frontend:
        database:
            user: frontend
            password: frontend
            host: localhost
            port: 5432
            name: frontend
        directories:
            base_path: /home/whoami/workspace/frontend
            backup_directories: [my/directory, other/directory]
        processor:
            compress: zip
            ratio: 9
            password: "okdaokdaopdpk"
            storages: [Dropbox]
        cloud_storages:
            dropbox_sdk:
            access_token: ~
            remote_path: "  /frontend"
    otherinstance:
        database:
            user: otherinstance
            password: otherinstance
            host: localhost
            port: 5432
            name: frontend
        directories:
            base_path: /home/whoami/workspace/otherinstance
            backup_directories: [my/directory, other/directory]
        processor:
            compress: zip
            ratio: 9
            password: "okdaokdaopdpk"
        storages: [Dropbox]
        cloud_storages:
            dropbox_sdk:
            access_token: ~
            remote_path: "  /otherinstance"
		
```
