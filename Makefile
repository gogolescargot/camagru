.PHONY: all up daemon down stop clean fclean logs

all: up

up:
	docker compose up --build

daemon:
	docker compose up --build -d

down:
	docker compose down

stop: down

clean:
	docker compose down -v
	rm -rf src/uploads src/public/uploads

fclean: clean
	rm -rf uploads/* src/public/uploads/.[!.]*

logs:
	docker compose logs -f

re: fclean all