CC=gcc
CFLAGS=-O2 -Wall

client: client.o
	${CC} ${CFLAGS} client.o -o client -lm -lssl -lcrypto

client.o: client.c
	${CC} ${CFLAGS} -c client.c

clean:
	rm -f client client.o .*.swp 
