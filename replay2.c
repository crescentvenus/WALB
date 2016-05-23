/*
gcc replay2.c -I/usr/local/include -L/usr/local/lib -lwiringPi -o replay2

SW_level_stat  0  ...main menu
SW_level_stat  1  ...Level Set     0,3,6,9,12....
SW_level_stat  2  ...Date & Time Set Now,ORG,Y3+,D1-,....

*/
#include <wiringPi.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <time.h>

#define NOT_FOUND -1
#define MAX_LINE 200
#define MAX_CHAR 200

/* Raspberry Pi 2  GPIO */
#define rA     0   // rotary encorder A
#define rB     1   // rotary encorder B
#define rRED   2   // LED on rotary encorder RED
#define rGREEN 3   // LED on rotary encorder GREEN
#define rSW    4   // Push SW on rotary encorder

int a, b;
int p_a, p_b;
int int_a=0, int_b=0, int_disp = 0;
int num=0, num2=0, num3=0,last_tx=1;
int SW_long = 500;
int SW_level_stat =0;

int now =0;  // timer to check gps-sdr-sim process status
int prev = 0;
time_t  rtime;

FILE *fp;
char *menu_name, *file_name, *freq, *s_rate, *date_time, *power;
char *p;
char s[MAX_CHAR];
/* M for menu, L for Level, D for Date&Time, P for Position */
int Mmax,Lmax,Dmax,Pmax;  // max number of lines on power level setting
char *Mname = "/home/pi/menu2.txt";
char *Lname = "/home/pi/level2.txt";
char *Dname = "/home/pi/date2.txt";

char Mbuf[MAX_LINE][MAX_CHAR];
char Lbuf[MAX_LINE][MAX_CHAR];
char Dbuf[MAX_LINE][MAX_CHAR];

char cmd[50]="i2c-disp.sh -i ";
char cmd2[50]="i2c-disp.sh -p 0x40 ";
char cmd3[50]="i2c-disp.sh -p 0x43 ";

int Trim(char *s) {
    int i;
    int count = 0;

    if ( s == NULL ) { /* yes */
        return -1;
    }
    i = strlen(s);
    while ( --i >= 0 && s[i] == '\n' ) count++;
    s[i+1] = '\0';
    i = 0;
    while ( s[i] != '\0' && s[i] == '\n' ) i++;
    strcpy(s, &s[i]);

    return i + count;
}

int strpos(char *haystack, char *needle)
{
   char *p = strstr(haystack, needle);
   if (p)
      return p - haystack;
   return NOT_FOUND;
}

void proc(int count){
    char str[MAX_CHAR];
    char tmp[MAX_CHAR];
    if(int_disp==0){
    int_disp=1;
    switch (SW_level_stat){
      case 0:
        num+=count;
        if(num < 0) num=Mmax-1;
             num = num % Mmax;
/* Get current menu item  */
/* char *menu_name, *file_name, *freq, *s_rate */
        strcpy(tmp,Mbuf[num]);
        menu_name=strtok(tmp,",");
        file_name=strtok(NULL,",");
        freq=strtok(NULL,",");
        s_rate=strtok(NULL,",");
             strcpy(str,cmd);
             strcat(str, menu_name);
             system(str);                   // Display 1st line on LCD
        if(atoi(s_rate)!=0 || num==0){      // Power
          strcpy(str,cmd2);                 // Display 2nd line on LCD
          strcat(str,Lbuf[num2]);
          system(str);

          strcpy(str,cmd3);
          strcat(str,Dbuf[num3]);
          system(str);
        }
        if( num==1 ){
          strcpy(str,cmd2);                 // Display 2nd line on LCD
          strcat(str,Dbuf[num3]);           // Date&Time
          system(str);
        }
        break;
      case 1:    // Sub menu --- Level set
        num2=disp_sub(count, num2, Lmax,"*TxPower", Lbuf);
        break;
      case 2:
        num3=disp_sub(count, num3, Dmax,"*DateTime", Dbuf);
        break;
      }
         int_disp=0;
    }
}

int disp_sub(int count, int pos, int max, char *msg1, char msg2[][MAX_CHAR]){
  char str[MAX_CHAR];

    pos+=count;
    if(pos < 0) pos=max-1;
    pos = pos % max;
    strcpy(str,cmd);
    strcat(str, msg1);
    system(str);            // Display 1st line
    strcpy(str,cmd2);
    strcat(str,msg2[pos]);
    system(str);            // Display 2nd line
  return pos;
}

void click_a(void){
    int _a;
    if (int_a ==0 ){
  int_a=1;
        _a = digitalRead(rA);//GPIOの値を取得。
        if(_a != a){ //同じ値が連続した場合にスキップする。
                if(a == 1){ //一つ前のA端子の値を格。
                        p_a = 1;
                }else{
                        p_a = 0;
                }
                a = _a;
                //a端子とb端子の直前の値が1であり、今の値が0である場合にTRUE。
                if(a == 0 && b == 0 && p_a == 1 && p_b == 1){
      proc(1);
                }
        }
  int_a=0;
    }
}
void click_b(void){
    int _b;
    if ( int_b == 0){
        int_b=1;
        _b = digitalRead(rB);
        if(_b != b){
                if(b == 1){
                        p_b = 1;
                }else{
                        p_b = 0;
                }
                b = _b;
                if(a == 0 && b == 0 && p_a == 1 && p_b == 1){
      proc(-1);
                }
        }
    int_b=0;
    }
}

void flashLED(int color, int counts){
  int i;
  for(i=0;i<counts; i++){
    digitalWrite(color,1);
    usleep(200000);
    digitalWrite(color,0);
    usleep(200000);
  }
}

int read_file(char *file, char buf[][MAX_CHAR]){
  FILE *fp;
  char tmp[MAX_CHAR];
  int n,pos;
  fp = fopen(file, "r" );
  if( fp == NULL ){
        printf( "%sファイルが開けません\n", file );
      return -1;
  }
  n=0;
  while( fgets( tmp, MAX_CHAR, fp ) != NULL ){
        if ( (pos = strpos( tmp, "#" )) !=0 ) {
      Trim(tmp);
//      printf("%s\n",tmp);
            strcpy(buf[n++],tmp);
    }
    }
  fclose(fp);
  return n;
}

int tx_blink(int tx){
  int result;
  result=system("/home/pi/stat.sh");
  if (WEXITSTATUS(result)==1){   // transmitting ?
    if(tx==0){
             digitalWrite(rRED,1);
             tx=1;
         } else {
             digitalWrite(rRED,0);
            tx=0;
    }
    } else {
    if(tx==1) {
      digitalWrite(rRED,0);
            tx=0;
    }
  }
  return tx;
}


int main(void){
    int setup = 0;
  int result;
  char str[100],str0[50],str1[50];
  char tmp[100];
  int i, n=0;
  int pos = NOT_FOUND;
  unsigned int maxMillis=1000;
  int tx=0;
        a=1;
        b=1;
        p_a=0;
        p_b=0;
        num=0;num2=0;
    setup = wiringPiSetup();
  pinMode(rRED,OUTPUT);
  pinMode(rGREEN,OUTPUT);
  pinMode(rSW,INPUT);
/* read main menu */
  printf("Main menu:%s\n",Mname);
  Mmax=read_file(Mname, Mbuf);
/* read level menu */
  Lmax=read_file(Lname, Lbuf);
/* read date&time menu */
  Dmax=read_file(Dname, Dbuf);

/* Get current menu item  */
/* char *menu_name, *file_name, *freq, *s_rate */
  strcpy(tmp,Mbuf[num]);
  menu_name=strtok(tmp,",");
  file_name=strtok(NULL,",");
  freq=strtok(NULL,",");
  s_rate=strtok(NULL,",");
  date_time="2016/04/15,00:00:00";
  printf("%s %s %s %s %s\n",menu_name,file_name,freq,s_rate,date_time);

/* menue Line1 */
  strcpy(str,cmd);
  strcat(str, menu_name);
  printf("%s",menu_name);
  system(str);    // Display 1st line //
/* menue Line 2 */
  strcpy(str,cmd2);
  strcat(str,Lbuf[num2]);
  system(str);    // Display 2nd line //
/* indicate boot finish */
  flashLED(rRED,2);
  flashLED(rGREEN,2);

    pullUpDnControl(rSW, PUD_UP);
    pullUpDnControl(rA, PUD_UP);
    pullUpDnControl(rB, PUD_UP);
    wiringPiISR(rA, INT_EDGE_BOTH, click_a);
    wiringPiISR(rB, INT_EDGE_BOTH, click_b);
    while(setup != -1){
        usleep(10000);
    if(digitalRead(rSW)==0) {    // Push SW pressed
//    printf("[%s %s %s %s %s]\n",menu_name,file_name,freq,s_rate,date_time);
//      printf("SW_level_stat:%d\n",SW_level_stat);
      n = 0;
      digitalWrite(rGREEN,1);    // light GREEN LED
      switch (SW_level_stat){
        case 1:           // Level setting
          SW_level_stat=0;
          num=last_tx;
          proc(0);
          break;
        case 2:           // Level setting
          SW_level_stat=0;
          num=last_tx;
          proc(0);
          break;
        case 0:          // Main menu selection
          switch (num){
            case 0:      // Power level setting
              SW_level_stat=1;
    //          proc(0);
              break;
            case 1:      // Date&Time setteing
              SW_level_stat=2;
    //          proc(0);
              break;
            case 2:      // Fixed Latitude & Longtude setting
              break;
            default:
              ;
          }
          proc(0);
          strcpy(str,file_name);
          for(i=0;i<100;i++){
            str[i]=0x00;
          }
          if(atoi(s_rate) !=0){        // sample
            if(strpos(file_name,".txt")>0 || strpos(file_name,".csv")>0){
              strcpy(str,"/home/pi/sim_start.sh ");// generate rela-time I/Q data
            } else {
              strcpy(str,"/home/pi/transmit2.sh ");  // Use pre-generated I/Q data
//              Trim(str);
//              strcat(str,file_name);          // Replay file name
            }
            Trim(str); strcat(str,file_name);    // Replay file name
            Trim(str); strcat(str," ");
             strcpy(str1,Lbuf[num2]);  // Replay POWER
             Trim(str1);
            strcat(str,str1);    // transmit2.sh file_name POWER
            strcat(str," "); strcat(str,freq);
            strcat(str," ");
             Trim(s_rate); strcat(str,s_rate);
            strcat(str," ");strcat(str,Dbuf[num3]);
            strcat(str,"&");    // transmit2.sh file_name POWER&
            Trim(str); last_tx=num;
//            printf("\n[Tx shell cmd:%s]\n",str);
            system(str);  // 実行
          } else {
            system(file_name);  // 単純なshell実行
          }
          break;
      }
      while (digitalRead(rSW)==0) {  // Waite until SW pressed
        usleep(1000);
        n++;
        if ( n > SW_long )  digitalWrite(rRED,1);
      }
      usleep(200000);        // Avoid chattering
    } else {
      digitalWrite(rGREEN,0);    // Now SW released, put off LED
    }

    if (n > SW_long ){      // KILL process when SW pushed long duration
        if(SW_level_stat==0){
        system("/home/pi/kill_proc.sh");
        digitalWrite(rRED,1);
        usleep(500000);
        digitalWrite(rRED,0);
        n=0;
      } else {
        SW_level_stat=0;
        proc(0);
      }
    }
    now = millis();
    if(( now - prev ) > 1000){  // LED update while Tx working
      tx=tx_blink(tx);
      prev = now;
    }

  }
}