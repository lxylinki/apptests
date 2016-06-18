#include <stdio.h>      /* sprintf */
#include <arpa/inet.h>  /* sockaddr_in, inet_addr */
#include <unistd.h>     /* close */
#include <string.h>     /* strlen, memset */
#include <ctype.h>      /* isdigit */
#include <math.h>       /* pow */
#include <stdlib.h>     /* EXIT_SUCCESS, EXIT_FAILURE*/
#include <netdb.h>      /* gethostbyname */
#include <openssl/ssl.h>
#include <openssl/err.h>

/* server name/address should be supplied from cmd line */
char* SERVER_NAME = "cs5700sp16.ccs.neu.edu";
char* SERVER_IP = "129.10.113.143";

/* standard prefix for format check */
char* STATUS_PREFIX = "cs5700spring2016 STATUS ";
char* BYE_PREFIX = "cs5700spring2016 ";

int SERVER_PORT = 27993;
int SERVER_SSL_PORT = 27994;

/* the maximum length of each message is 256 bytes*/
#define MAX_LEN 256
#define HELLO_FORMAT "cs5700spring2016 HELLO %s\n"
/* STATUS
 * cs5700spring2016 STATUS [number] [math op] [number]\n
 *
 * SOLUTION
 * cs5700spring2016 [number]\n
 */
#define SOLUTION_FORMAT "cs5700spring2016 %d\n"
/* BYE
 * cs5700spring2016 [a 64 byte secret flag] BYE\n
 * */


/* total num of chars in a message: 
 * include the first terminating '\n' */
int msg_len(const char* msg) {
    int i = 0;
    /* regular strs are terminated by '\0' */
    while( (msg[i] != 0x00) && (i < MAX_LEN) ) {
        /* if reached first line feed:
         * count it and consider it as termination 
         * */
        if (msg[i] == 0x0A) {
            i += 1;
            break;
        } else {
            /* no line feed yet */
            i += 1;
        }
    }
    return i;
}

/* compare two strings upto nth character */
int equal_strs(const char* str1, const char* str2, int n) {
    int result = 1;
    int i = 0;
    while(i < n) {
        if (str1[i] != str2[i]) {
            result = 0;
            break;
        } else {
            i += 1;
        }
    }//end while
    return result;
}

/* 0. if msg length is within given range (MAX_LEN=256) */
int valid_len(const char* msg, int max_len) {
    int result = 1;
    if (msg_len(msg) > max_len) {
        result = 0;
    }
    printf("Invalid message: length over 256 bytes.\n");
    return result;
}

/* 2. if prefix matches standard
 * example:
 * STATUS: "cs5700sping2016 STATUS "
 * BYE: "cs5700spring2016 "
 * */
int valid_prefix(const char* msg, const char* std_pref) {
    int result = 1;
    /* prefix length */
    int pref_len = msg_len(std_pref);
    if ( msg_len(msg) < pref_len ) {
        result = 0;
    } else {
        int i;
        for (i=0; i<pref_len; i++) {
            if (msg[i] != std_pref[i]) {
                //printf("Invalid message: prefix mismatch.\n");
                result = 0;
                break;
            }
        }//end for
    }
    return result;
}

/* if a char is valid operator */
int valid_op(char op) {
    int result = 0;
    char all_ops[4] = {'+', '-', '*', '/'};
    int i;
    for(i=0; i<4; i++) {
        if(op == all_ops[i]) {
            result = 1;
            break;
        }
    }
    return result;
}

/* 2.3, 2.5 if a msg segment [idx1, idx2] represents valid number 
 * */
int valid_num(const char* msg, int idx1, int idx2) {
    int i;
    /* 0 for invalid, 1 for valid */
    int result = 1;
    for (i=idx1; i<=idx2; i++) {
        if (!isdigit(msg[i])) {
            printf("Error: message segment is not a valid number.\n");
            result = 0;
            break;
        }
    }// end for
    return result;
}

/* convert a valid msg segment [idx1, idx2] to numerical value*/
int str_num(const char* msg, int idx1, int idx2) {
    int value = 0;

    /* power of 10 */
    int k = idx2-idx1;
    int i;
    for (i=idx1; i<=idx2; i++) {
        /* get int value */
        int n = msg[i]-'0';
        /* printf("%d, %d,\n", n, k); */
        value += n*pow(10, k);
        k -= 1;
    }
    return value;
}

/* print string with visible spaces and line feed */
void print_str(const char* msg) {
    int i = 0;
    while(msg[i] != 0x00) {
        /* if not alphabet or number */
        if (!isalnum(msg[i])) {
            if (valid_op(msg[i])) {
                printf("%c", msg[i]);
            } else {
                printf("[%X]", msg[i]);
            }
        } else {
            printf("%c", msg[i]);
        }
        i += 1;
    }
    printf("\n");
}

/* 2.2-2.5 if math expression is valid
 * example STATUS suffix: "56 / 285"  
 *
 * 2.2 spaces indices diff is 2
 * 2.3 index [0-1st sp): digits
 * 2.4 index (1st sp, 2nd sp): one of '+', '-', '*', '/'
 * 2.5 index (2nd sp-'\n'): digits
 * 2.6 end with one '\n'
 * */
int valid_STATUS_suffix(const char* msg, const char* std_pref) {
    int result = 1;

    /* find index of first and last char in suffix */ 
    int start_idx = msg_len(std_pref); 
    int end_idx = msg_len(msg)-1; 

    /* check if ended with '\n' */
    if (msg[end_idx] != 0x0A) {
        printf("Invalid message: not ended with line feed.\n");
        result = 0;
        return result;
    }

    /* start from prefix end, find indices of the last 2 spaces */
    int sps = 0;
    int i; 
    int sp1 = 0;
    int sp2 = 0;
    for (i=start_idx; i<end_idx; i++) {
        /* count spaces */
        if (msg[i] == 0x20) {
            sps += 1;
        } else {
            continue;
        }
        /* the 1st space */
        if (sps == 1) {
            sp1 = i;
        } else if (sps == 2) {
            /* the 2nd space */
            sp2 = i;
            /* if they differ by 2 */
            if (sp2-sp1 != 2) {
                /* if not: invalid suffix */
                //printf("Invalid message: spaces misaligned in suffix.\n");
                result = 0;
                break;
            } else {
                /* with start_idx, sp1, sp2, end_idx, check segments */
                int num1_valid = valid_num(msg, start_idx, sp1-1);

                /* note that string is ended with '\n' */
                int num2_valid = valid_num(msg, sp2+1, end_idx-1);
                
                int op_valid = valid_op(msg[sp1+1]);

                /* all three fields need to be valid */
                if (! (num1_valid * op_valid * num2_valid)) {
                    /*
                    printf("Invalid field: num1 %d, math op %d, num2 %d\n", num1_valid, op_valid, num2_valid);
                    printf("MSG len: %d\n", end_idx+1);
                    printf("start_idx=%d, sp1=%d\nsp2=%d, end_idx=%d\n",
                            start_idx, sp1, sp2, end_idx);
                            */
                    result = 0;
                    break;
                }
            }
        } else {
            /* invalid suffix */
            result = 0;
            break;
        }
    }// end for
    return result;
}

/* compute solution for math expression in validated msg */
int solution(const char* msg, const char* std_pref) {
    /* find index of first and last char in suffix */ 
    int start_idx = msg_len(std_pref); 
    int end_idx = msg_len(msg)-1; 

    /* start from prefix end, find indices of the last 2 spaces */
    int sps = 0;
    int i; 
    int sp1=0; 
    int sp2=0;
    char op = 0x00;
    /* fill sp1, sp2, op */
    for (i=start_idx; i<=end_idx; i++) {
        /* count spaces */
        if (msg[i] == 0x20) {
            sps += 1;
        } else {
            continue;
        }

        /* the 1st space */
        if (sps == 1) {
            sp1 = i;
        } else if (sps == 2) {
            /* the 2nd space */
            sp2 = i;
            /* or sp2-1 */
            op = msg[sp1+1];
        }
    }//end for

    int solution = 0;
    int l_num = str_num(msg, start_idx, sp1-1);
    int r_num = str_num(msg, sp2+1, end_idx-1);

    if (op == '+') {
        solution = l_num + r_num;
    } else if (op == '-') {
        solution = l_num - r_num;
    } else if (op == '*') {
        solution = l_num * r_num;
    } else if (op == '/') {
        /* to nearest integer */
        solution = round(l_num/r_num);
    }
    return solution;
}

/* valid BYE suffix 
 * "[a 64 bytes secret flag] BYE\n"
 * */ 
int valid_BYE_suffix(const char* msg, const char* std_pref) {
    int result = 1;

    /* find index of first and last char in suffix */ 
    int start_idx = msg_len(std_pref); 
    int end_idx = msg_len(msg)-1;

    /* check if ended with '\n' */
    if (msg[end_idx] != 0x0A) {
        printf("Invalid message: not ended with '\n'\n");
        result = 0;
        return result;
    }

    /* find the space in suffix
     * 1. only 1 space
     * 2. at 64-the byte from suffix start
     * */
    int i;
    int sps = 0;
    for (i=start_idx; i<end_idx; i++) {
        /* count spaces */
        if (msg[i] == 0x20) {
            sps += 1;
            if (sps > 1) {
                /* too many spaces: invalid */
                //printf("Invalid message (BYE?): spaces misaligned in suffix.\n");
                result = 0;
                return result;

            } else if (sps == 1) {
                /* the 1st space is at 64-th byte from suffix start */
                if ( (i-start_idx) != 64) {
                    /* invalid if not 64 bytes */
                    //printf("Invalid message (BYE?): not 64 bytes flag.\n");
                    result = 0;
                    return result;
                }
            }
        }
    }// end for

    /* if no space found: invalid */
    if (sps == 0) {
        printf("Invalid message: not enough number of spaces.\n");
        result = 0;
        return result;
    }

    /* found one space at 64-th byte */
    /* check BYE: start_idx + 65, 66, 67 */
    if (msg[start_idx+65] != 'B') {
        printf("Invalid message: not match BYE suffix.\n");
        result = 0;
        return result;
    }

    if (msg[start_idx+66] != 'Y') {
        printf("Invalid message: not match BYE suffix.\n");
        result = 0;
        return result;
    }

    if (msg[start_idx+67] != 'E') {
        printf("Invalid message: not match BYE suffix.\n");
        result = 0;
        return result;
    }
    return result;
}

/* conduct a STATUS format check on received msg:
 * 0. length < 256
 * 1. terminated with '\n'
 *
 * 2. check each substring 
 *    2.1 "cs5700spring2016 STATUS " 
 *
 *    2.2 [1.0, 1000.0] 
 *    2.3-4 one of + - * /
 *    2.5 [1.0, 1000.0]
 * */
int valid_STATUS(const char* msg, const char* STATUS_pref) {
    int result = 1;
    /* if both prefix and STATUS suffix are valid */
    int prefix_valid = valid_prefix(msg, STATUS_pref);
    if (! prefix_valid) {
        //printf("Not match with STATUS prefix.\n");
        result = 0;
        return result;
    }

    int suffix_valid = valid_STATUS_suffix(msg, STATUS_pref);
    if (! (prefix_valid * suffix_valid)) {
        //printf("Not match with STATUS suffix.\n");
        result = 0;
    }
    return result;
}

/* conduct a BYE format check on received msg */
int valid_BYE(const char* msg, const char* BYE_pref) {
    int result = 1;
    /* if both prefix and BYE suffix are valid */
    int prefix_valid = valid_prefix(msg, BYE_pref);
    if (! prefix_valid) {
        //printf("Not match with BYE prefix.\n");
        result = 0;
        return result;
    }
    int suffix_valid = valid_BYE_suffix(msg, BYE_pref);
    if (! (prefix_valid * suffix_valid)) {
        //printf("Not match with BYE suffix.\n");
        result = 0;
    }
    return result;
}

/* print 64 byte secret flag in one line */
void print_flag(const char* valid_bye) {
    int len = msg_len(valid_bye);
    /* number of spaces */
    int sps = 0;
    /* if start printing */
    int start_print = 0;
    int i;
    for (i=0; i<len; i++) {
        if (valid_bye[i] == 0x20) {
            sps += 1;
            if (sps == 1) {
                start_print = 1;
                continue;
            } else if (sps == 2) {
                break;
            }
        }// end if
        if (start_print) {
            printf("%c", valid_bye[i]);
        }
    } // end for
    printf("\n");
}

/* check number of cmd arguments */
int valid_argc(int argc) {
    int result = 0;
    int valid_num[4] = {3, 4, 5, 6};
    int i;
    for (i=0; i<4; i++) {
        if (argc == valid_num[i]) {
            result = 1;
            break;
        }
    }
    return result;
}

/* usage info */
void print_usage(void) {
    printf("Usage:   ./client <-p port> <-s> [hostname] [NEU ID]\n\n");
    printf("Example: ./client 129.10.113.143 001714204\n");
    printf("         ./client cs5700sp16.ccs.neu.edu 001676940\n");
    printf("         ./client -p 27993 129.10.113.143 001714204\n");
    printf("         ./client -s 129.10.113.143 001676940\n");
    printf("         ./client -p 27994 -s 129.10.113.143 001714204\n\n");
}

/* hostname to IP address: 
 * accept either hostname or ip address
 * */
unsigned long name_ip(const char* hostname) {
    struct hostent* host;
    if ((host = gethostbyname(hostname)) == NULL) {
        perror("Hostname to IP address translation error");
        exit(EXIT_FAILURE);
    }
    return *( (unsigned long*) host->h_addr_list[0] );
}

/* establish TCP connection towards given host */
int TCP_connect(const char* hostname, int port) {

    /* socket descriptor */
    int client_sock = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);
    if (client_sock < 0) {
        perror("Client socket error");
        exit(EXIT_FAILURE);
    }

    /* server address data */
    struct sockaddr_in server_addr; 

    memset(&server_addr, 0, sizeof(server_addr));
    server_addr.sin_family = AF_INET;
    server_addr.sin_port = htons(port);
    server_addr.sin_addr.s_addr = name_ip(hostname);
    
    /* establish a connection */ 
    int is_con = connect(client_sock, (struct sockaddr*) &server_addr, sizeof(server_addr));
    if (is_con < 0) {
        perror("Server socket connection error");
        exit(EXIT_FAILURE);
    }

    /* return the connected socket */
    return client_sock;
}

/* set/overwrite global variables:
 * SERVER_NAME
 * SERVER_PORT
 * and ssl option from valid cmd line */
void config_server_info(int argc, char* argv[], int* if_ssl) {
    if (argc == 3) {
        SERVER_NAME = argv[1];

    } else if (argc == 5) {
        int port_len = msg_len(argv[2]);
        SERVER_PORT = str_num(argv[2], 0, port_len-1);
        SERVER_NAME = argv[3];

    } else if (argc == 4) {
        *if_ssl = 1;

    } else if (argc == 6) {
        *if_ssl = 1;
        int port_len = msg_len(argv[2]);
        SERVER_SSL_PORT = str_num(argv[2], 0, port_len-1);
        SERVER_NAME = argv[4];
    }
}

/* init openssl */
void Init_OpenSSL(void) {
    SSL_load_error_strings();
    SSL_library_init();
    OpenSSL_add_all_algorithms();
}

/* create new SSL_CTX object */
SSL_CTX* Init_CTX(void) {
    SSL_CTX* ctx = SSL_CTX_new(SSLv23_method());
    if (ctx == NULL) {
        ERR_print_errors_fp(stderr);
        exit(EXIT_FAILURE);
    }
    return ctx;
}


int main(int argc, char* argv[]) {
    /* check num of cmd line options */
    if (! valid_argc(argc)) {
        print_usage();
        return EXIT_FAILURE;
    }
    /* no ssl by default */
    int ssl_required = 0;
    /* overwrite default info */
    config_server_info(argc, argv, &ssl_required);
    
    /* prepare for ssl connection */
    Init_OpenSSL();
    SSL_CTX* ctx = Init_CTX();
    /* create new SSL connection state */
    SSL* ssl = SSL_new(ctx);
    
    int client_sock;
    if(ssl_required) {
        /* create TCP socket connection */
        client_sock = TCP_connect(SERVER_NAME, SERVER_SSL_PORT);
        /* attach to the socket descriptor */
        SSL_set_fd(ssl, client_sock);
        /* establish ssl connection */
        if ( SSL_connect(ssl) < 0) {
            ERR_print_errors_fp(stderr);
	    }
    } else {
        client_sock = TCP_connect(SERVER_NAME, SERVER_PORT);
    }

    /* prepare buffers: 
     * HELLO, SERVER_MSG(STATUS/BYE), SOLUTION */
    char HELLO[MAX_LEN];
    int hello_len = sprintf(HELLO, HELLO_FORMAT, argv[argc-1]);
    if (hello_len < 0) {
        perror("Formating HELLO error");
        exit(EXIT_FAILURE);
    }
    char SERVER_MSG[MAX_LEN];
    char SOLUTION[MAX_LEN];

    /* variables to keep track of function results */
    int hello_sent, sol, sol_sent, can_receive, sol_len; 
    
    
    
    /* Initialize protocol:
     * send HELLO to server */
    if (ssl_required) {
        hello_sent = SSL_write(ssl, HELLO, msg_len(HELLO));
        if (hello_sent <= 0) {
            perror("SSL writing HELLO to server error");
        }
    } else {
        hello_sent = send(client_sock, HELLO, msg_len(HELLO), 0);
        if (hello_sent < 0) {
            perror("Sending HELLO to server error");
        }
    }
    //printf("%s", HELLO);

    /* DO recv MSG from server while not receive valid BYE */
    do{
        if (ssl_required) {
            can_receive = SSL_read(ssl, SERVER_MSG, MAX_LEN);
            if (can_receive <= 0) {
                perror("SSL reading from server error");
            }
        } else {
            can_receive = recv(client_sock, SERVER_MSG, MAX_LEN, 0);
            if (can_receive < 0) {
                perror("Receiving from server error");
            } else if (can_receive == 0) {
                printf("Wrong answer. Remote connection closed.\n");
            }
        }

        /* print what is received */
        //printf("%s", SERVER_MSG);

        /* check message format */
        if ( valid_STATUS(SERVER_MSG, STATUS_PREFIX) ) {
            /* if STATUS:
             * compute solution
             * format SOLUTION
             * send SOLUTION
             * */
            sol = solution(SERVER_MSG, STATUS_PREFIX);
            sol_len = sprintf(SOLUTION, SOLUTION_FORMAT, sol);
            if (sol_len < 0) {
                perror("Formating SOLUTION error");
            }
            /* print what is to be sent */
            //printf("%s", SOLUTION);

            if (ssl_required) {
                sol_sent = SSL_write(ssl, SOLUTION, msg_len(SOLUTION));
                if (hello_sent <= 0) {
                    perror("SSL writing HELLO to server error");
                }
            } else {
                sol_sent = send(client_sock, SOLUTION, msg_len(SOLUTION), 0);
                if (sol_sent < 0) {
                    perror("Sending SOLUTION to server error");
                }
            }

        } else if ( valid_BYE(SERVER_MSG, BYE_PREFIX) ) {
            /* if BYE: 
             * print 64 bytes flag in one line
             * close connection
             * */
            /* print what is received */
            //printf("Received BYE: %s", SERVER_MSG);
            print_flag(SERVER_MSG);
            /* exit: close the connection */
            break;
        } else { 
            /* print what is received */
            printf("Error! Received invalid message:\n");
            print_str(SERVER_MSG);
            break;
        }
    } while ( !valid_BYE(SERVER_MSG, STATUS_PREFIX) );




    /* close the connection only when received BYE */
    int is_closed = close(client_sock);
    if (is_closed < 0) {
        perror("Close socket error");
    }
    SSL_free(ssl);
    SSL_CTX_free(ctx);
    return EXIT_SUCCESS;
}
