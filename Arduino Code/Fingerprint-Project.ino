    #include <Adafruit_Fingerprint.h>
    #include <Wire.h>
    #include <String.h>
    #include <LiquidCrystal_I2C.h>
    #include <Keypad.h>
    #define mySerial Serial1
    SoftwareSerial mySerial(2, 3);//converts pin 2 and 3 to rx and tx for spi communication protocol
    using namespace std;//namespace for string functions and types
    const int  en = 2, rw = 1, rs = 0, d4 = 4, d5 = 5, d6 = 6, d7 = 7, bl = 3;//initializes the pins used by the I2C on the LCD
    const int i2c_addr = 0x27;//the I2C address initialization
    LiquidCrystal_I2C lcd(i2c_addr, en, rw, rs, d4, d5, d6, d7, bl, POSITIVE);
    //keypad initiatilization
    const byte ROWS = 4; //four rows
    const byte COLS = 3; //three columns
    char keys[ROWS][COLS] = {
      {'1','2','3'},
      {'4','5','6'},
      {'7','8','9'},
      {'*','0','#'}
    };
    bool adminmode = true;//variable for setting the admin mode true or false
    byte rowPins[ROWS] = {13, 12, 11, 10}; //connect to the row pinouts of the keypad
    byte colPins[COLS] = {6, 7, 8};//connect to the column pinouts of the keypad
    Keypad keypad = Keypad( makeKeymap(keys), rowPins, colPins, ROWS, COLS );
    Adafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);//the variables from software serial are passed to fingerprint library object
    //global variables init
    int password;//variable for holding admin passcode
    int id;//the id of searched fingerprint
    int admin;// THE ADMIN ID
    char keyPad = '3';//holds default char for keyPad
    int mode = 0;//
    bool searched = false;
    int buzz = 9;//buzzer makes noise when unauthorized personnel tries to access admin operations
    int alert = 4;//an led to alert as the buzzer
    int lock = 5;//an alert to show access granted
    bool lockmode = false;//only true if the program is at search mode
    void setup() //runs once 
    {
      Serial.begin(9600);
      delay(100);
      pinMode(buzz, OUTPUT);//setting buzzer as output
      pinMode(alert, OUTPUT);//red led for denied/not found fingerprint
      pinMode(lock, OUTPUT);//led to show searched print found
      finger.begin(57600);//setting baud rate for scanner
      delay(5);
      init_system();//initialisation informing modes of operation
    }
    void loop()
    { 
     processing();//the main function that calls other functions
    }
    //*****************************INITIALIZATION********************************************
     void init_system(){
            lcd.begin(16,2);//initialize lcd object
            lcd.setCursor(0,0);
            if (finger.verifyPassword()) {//checks for successful communication of scanner and microcontroller
                lcd.print("SCANNER FOUND!");
            } else {
              lcd.print("SCANNER NOT FOUND!");
              while (1) { delay(1); }
            }
            delay(1000);
           lcd.clear();
           lcd.setCursor(1,0);
           lcd.print("-WELCOME TO THE-");
           lcd.setCursor(0,1);
           lcd.print("EXAMINATION HALL");
           delay(2000);
         lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("ENROLL = 1");
        lcd.setCursor(0, 1);
        lcd.print("DELETE = 4");
        delay(1000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SEARCH = 2 ");
        lcd.setCursor(0, 1);
        lcd.print("EMPTY DB = 5");
        delay(1000);
      }
    //*************** The main custom function*************************
    void processing(){
        enquireMode();//Enquires/informs what mode of operation the user wants: search, enroll, delete or empty database
        keyPad = keypad.getKey();
          while(!keyPad || keyPad == '3')//loops until key is pressed
              {
                keyPad = keypad.getKey();
              }
          if(keyPad == '1'){//if 1 was keyyed
                 admin = 0;//initializes the admin variable with id 0
                 authenticate_admin();//calls function to verify admin before enrollment; it returns admin fingerprint id
                if(admin == 20 || admin == 30 || admin == 40 || admin == 50 || admin == 60 || admin == 70 || admin == 80 || password == 1995)
                {  
                         lcd.clear();
                         lcd.setCursor(0, 0);
                         lcd.print("-ENROLLMENT MODE-");
                         delay(2000);
                         searched = false;
                          bool cont = true;//keeps the mode as enrollment as long as it's true
                         while(cont)
                          { 
                         lcd.clear();
                         lcd.setCursor(0, 0);
                         lcd.print("-TYPE THE ID-");
                         lcd.setCursor(0, 1);
                         lcd.print("PRESS # TO ENTER");
                         id = GetNumber();//id to be enrolled
                         getFingerprintEnroll();//calls enrolment function
                         keyPad = keypad.getKey();
                         lcd.clear();
                         lcd.setCursor(0,0);
                         lcd.print("To Continue");
                         lcd.setCursor(0,1);
                         lcd.print("Press #");
                         keyPad = keypad.getKey();//resets the variable
                         while(!keyPad)//waits for keypress to continue with enrol or end
                          {
                            keyPad = keypad.getKey();
                          }
                          if(keyPad == '#')
                          {
                            cont = true;
                          }
                          else
                          {
                            cont = false;
                          }
                    }
               }
               else{//when admin is not verified
                   lcd.clear();
                   lcd.setCursor(0, 0);
                   lcd.print("-NOT AUTHORIZED !-");
                   tone(buzz, 1000);//buzzer makes noise
                   digitalWrite(alert, HIGH);//red LED alerts
                   delay(2000);
                   noTone(buzz);
                   digitalWrite(alert, LOW);
               }
          }
             if(keyPad == '2'){
                 lcd.clear();
                 lcd.setCursor(0, 0);
                 lcd.print("-SEARCH MODE-"); 
                 delay(1000);
                 lockmode = true;//ensures that the blue LED is turned on with successful found id for search mode only
                 adminmode = false;//ensures the admin verification codes are not executed inside the search function
                 int candidate = getFingerprintID();//calls search function
                 lockmode = false;
                 adminmode = true;
                 keyPad = keypad.getKey();//resets keyPad Variable
             }
             if(keyPad == '4')
             {
                admin = 0;
                authenticate_admin();//calls function to verify admin
                if(admin == 20 || admin == 30 || admin == 40 || admin == 50 || admin == 60 || admin == 70 || admin == 80 || password == 1995)
              {//if the admin was verified
                bool cont = true;
                while(cont)
                {
                     lcd.clear();
                     lcd.setCursor(0, 0);
                     lcd.print("-DELETE ONE -"); 
                     delay(1000);
                     lcd.clear();
                     lcd.setCursor(0, 0);
                     lcd.print("-ENTER ID -");
                     delay(1000);
                     int a = GetNumber();//stores id of the id to delete
                     lcd.clear();
                     lcd.setCursor(0, 0);
                     lcd.print("Deleting ID ");
                     lcd.setCursor(0, 1);
                     lcd.print(a);
                     deleteFingerprint(a);//calls function to delete a fingerprint with id 'a'
                     delay(1000);
                     keyPad = keypad.getKey();//resets the keypad variable to empty/unset value
                     lcd.clear();
                     lcd.setCursor(0,0);
                     lcd.print("To Continue");
                     lcd.setCursor(0,1);
                     lcd.print("Press #");
                     keyPad = keypad.getKey();
                     while(!keyPad)//waits for keypress until the user selects the mode
                      {
                        keyPad = keypad.getKey();
                      }
                      if(keyPad == '#')//if true the mode remains delete
                      {
                        cont = true;
                      }
                      else
                      {
                        cont = false;
                      }
                }
              }
              else{// alerts user is not admin
                   lcd.clear();
                   lcd.setCursor(0, 0);
                   lcd.print("-NOT AUTHORIZED !-");
                   tone(buzz, 1000);
                   digitalWrite(alert, HIGH);
                   delay(2000);
                   noTone(buzz);
                   digitalWrite(alert, LOW);
               }
             }
             if(keyPad == '5')
             {
                 admin = 0;
                authenticate_admin();//calls to verify admin
                if(admin == 20 || admin == 30 || admin == 40 || admin == 50 || admin == 60 || admin == 70 || admin == 80 || password == 1995)
                    {
                       lcd.clear();
                       lcd.setCursor(0, 0);
                       lcd.print("-DELETE ALL -"); 
                       delay(1000);
                       emptydb();
                       keyPad = keypad.getKey();//resetting the keyPad variable
                    }
                    else{
                       lcd.clear();
                       lcd.setCursor(0, 0);
                       lcd.print("-NOT AUTHORIZED !-");
                       tone(buzz, 1000);
                       digitalWrite(alert, HIGH);
                       delay(2000);
                       noTone(buzz);
                       digitalWrite(alert, LOW);
                   }
             }
             else
             {
                 lcd.clear();
                 lcd.setCursor(0, 0);
                 lcd.print("...");
                 keyPad = keypad.getKey();
             }
        }
    //********************************Auth Admin****************************************
    void authenticate_admin()//this function verifies admin
    {
       lcd.clear();
       lcd.setCursor(0, 0);
       lcd.print("USE-CODE = *");
       lcd.setCursor(0, 1);
       lcd.print("USE-PRINT= 0");
       while(!keyPad || keyPad == '1' || keyPad == '4' || keyPad == '5')
       {//waits for user to select mode of admin verification
        keyPad = keypad.getKey();
       } 
       if(keyPad == '*')
       {//if chose passcode
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("-TYPE THE CODE-");
            lcd.setCursor(0, 1);
            lcd.print("PRESS # TO ENTER");
            password = GetNumber(); 
       }
       else
       {
        admin = getFingerprintID();
       }
    }
    //****************Choosing mode of operation*********************
    void enquireMode()
    { //informs of modes of operation
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SCH = 2 ");
        lcd.setCursor(8, 0);
        lcd.print("ENR = 1 ");
        lcd.setCursor(0, 1);
        lcd.print("DEL = 4");
        lcd.setCursor(8, 1);
        lcd.print("EMPT= 5");
    }//**********************end of choosing mode********************
    
    //*************************reading keypad data*******************
     int GetNumber()
    {
       int numba = 0;
       char pressedkey = keypad.getKey();//resets key variable
        while(pressedkey != '#')//wait for keypad data until # is pressed to end
       {
          switch (pressedkey)
          {
             case NO_KEY:
                break;
             case '0': case '1': case '2': case '3': case '4':
             case '5': case '6': case '7': case '8': case '9':
                lcd.clear();
                lcd.setCursor(0, 1);
                lcd.print(pressedkey);//prints pressed key
                //ASCII for 0 is 48 and increments by 1 from 1 hence we must deduct 48 then add to the following place value
                numba = numba * 10 + (pressedkey - '0');
                break;
             case '*':
                numba = 0;
                lcd.clear();
                break;
          }
           //h++;
          pressedkey = keypad.getKey();
       }
       return numba;
    }
    //*******************************EMPTYING DB******************************************
    void emptydb()
    {
       lcd.clear();
                 
                    lcd.clear();
                    lcd.setCursor(0, 0);
                    lcd.print("Confirm Delete");
                    lcd.setCursor(0, 1);
                    lcd.print("All: YES=9, NO=8");
                 while(keyPad == '0' || keyPad == '*' || !keyPad)//loops until keypad value changes
                 {
                    keyPad = keypad.getKey();
                 }
                    if(keyPad == '9')//if confirmed delete all prints
                     {
                       finger.emptyDatabase();
                       lcd.clear();
                       lcd.setCursor(0, 0);
                       lcd.print("Emptied");
                       lcd.setCursor(0, 1);
                       lcd.print("Successfully!");
                       keyPad = '3';
                       delay(1000);
                     }
                    if(keyPad == '8')
                     {
                       lcd.clear();
                       lcd.setCursor(0, 0);
                       lcd.print("Cancelled!");
                       delay(1000);
                       keyPad = '3';
                     }
    }
    //****************DELETE A PRINT**************************
    uint8_t deleteFingerprint(uint8_t id) {//function to delete a given print
      uint8_t p = -1;
      p = finger.deleteModel(id);
      if (p == FINGERPRINT_OK) {
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Deleted!");
      } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Communication error");
        return p;
      } else if (p == FINGERPRINT_BADLOCATION) {
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Could not delete");
        lcd.setCursor(0,1);
        lcd.print("in that location");
        return p;
      } else if (p == FINGERPRINT_FLASHERR) {
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Error writing");
        return p;
      } else {
        lcd.clear();
        lcd.setCursor(0,0);
        lcd.print("Unknown error: 0x");
        lcd.setCursor(0,1);
        lcd.print(p, HEX);
        return p;
      }   
    }
    //*******************************************************Enrolllment of new prints**************************************************************
    uint8_t getFingerprintEnroll() {
      bool mismatch = true;
      while(mismatch){
        //loops until there is no mismatch of prints of same finger; allows enrolling with same id until successful
      int p = -1;
           lcd.clear();
           lcd.setCursor(0,0);
           lcd.print("Place finger...");
           delay(1000);
      while(p != FINGERPRINT_OK) {
        p = finger.getImage();
        switch (p) {
        case FINGERPRINT_OK:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Image taken!");
          break;
        case FINGERPRINT_NOFINGER:
          break;
        case FINGERPRINT_PACKETRECIEVEERR:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Communication Error.");
          break;
        case FINGERPRINT_IMAGEFAIL:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Imaging error.");
          break;
        default:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Unknown error!");
          break;
        }
        delay(1000);
      }
      p = finger.image2Tz(1);
      switch (p) {
        case FINGERPRINT_OK:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Image converted");
          break;
        case FINGERPRINT_IMAGEMESS:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Image too messy");
          return p;
        case FINGERPRINT_PACKETRECIEVEERR:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Communication error");
          return p;
        case FINGERPRINT_FEATUREFAIL:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("fingerprint");
      lcd.setCursor(0,1);
      lcd.print("features unfound");
          return p;
        case FINGERPRINT_INVALIDIMAGE:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("fingerprint");
      lcd.setCursor(0,1);
      lcd.print("features unfound");
          return p;
        default:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error!");
          return p;
      }
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Remove finger");
      delay(1000);
      p = 0;
      while (p != FINGERPRINT_NOFINGER) {
        p = finger.getImage();
      }
     lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("ID ");
      lcd.print(id);
      p = -1;
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Place same ");
      lcd.setCursor(0, 1);
      lcd.print("finger again");
      while (p != FINGERPRINT_OK){
        p = finger.getImage();
        switch (p){
        case FINGERPRINT_OK:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Image taken!");    
          break;
        case FINGERPRINT_NOFINGER:
          break;
        case FINGERPRINT_PACKETRECIEVEERR:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Communication Error.");
          break;
        case FINGERPRINT_IMAGEFAIL:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Imaging error.");
          break;
        default:
           lcd.clear();
           lcd.setCursor(0,0);
           lcd.print("Unknown error!");
          break;
        }
        delay(1000);
      }
      // OK success!
      p = finger.image2Tz(2);
      switch (p) {
        case FINGERPRINT_OK:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Image converted");
          break;
        case FINGERPRINT_IMAGEMESS:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Image too messy");
          break;
         // return p;
        case FINGERPRINT_PACKETRECIEVEERR:
           lcd.clear();
           lcd.setCursor(0,0);
           lcd.print("Communication error");
           break;
          //return p;
        case FINGERPRINT_FEATUREFAIL:
           lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("fingerprint");
      lcd.setCursor(0,1);
      lcd.print("features unfound");
      break;
          //return p;
        case FINGERPRINT_INVALIDIMAGE:
           lcd.clear();
           lcd.setCursor(0,0);
          lcd.print("fingerprint");
      lcd.setCursor(0,1);
      lcd.print("features unfound");
      break;
         // return p;
        default:
          //Serial.println("Unknown error");
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error!");
          break;
          //return p;
          delay(1000);
      }
      searched = false;
      //*******checks if the finger was already enrolled before.***************************
     // OK converted!
      p = finger.fingerFastSearch();
      if (p == FINGERPRINT_OK) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Found a Print");
          lcd.setCursor(0,1);
          lcd.print("Already Enrolled.");
          searched = true;
          delay(2000);
      } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Communication error!");
          delay(2000);
        return p;
      } else if (p == FINGERPRINT_NOTFOUND) {
          delay(2000);
      } else {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error");
          delay(2000);
        return p;
      }
      //******************************************end of already present checked*************************
      if(!searched)
      {
      // OK converted!
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Processing model");
          lcd.setCursor(0,1);
          lcd.print("For # ");
          lcd.print(id);
          delay(1000);
      p = finger.createModel();//this creates templates with two prints
      if (p == FINGERPRINT_OK) {
          lcd.clear();
          lcd.setCursor(0,0);
          mismatch = false;//states that there was no mismatch hence loop will terminate
          lcd.print("Prints Matched!");
          delay(2000);
      } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Communication error");
          delay(2000);
        //return p;
      } else if (p == FINGERPRINT_ENROLLMISMATCH) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Prints Did Not");
           lcd.setCursor(0,1);
          lcd.print("match, Try again..");
          delay(1500);
          //break;
        //return p;
      } else {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error");
          delay(1000);
        //return p;
          }
        }  
         if(!mismatch)
         {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("ID : ");
          lcd.print(id);
          delay(1000);
      p = finger.storeModel(id);
      if (p == FINGERPRINT_OK) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Template Stored!");
          delay(1000);
      } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
           lcd.clear();
           lcd.setCursor(0,0);
           lcd.print("Communication error");
           delay(1000);
        return p;
      } else if (p == FINGERPRINT_BADLOCATION) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Could not store");
          lcd.setCursor(0,1);
          lcd.print("in that location");
          delay(1000);
        return p;
      } else if (p == FINGERPRINT_FLASHERR) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Error writing to flash");
          delay(1000);
        return p;
      } else {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error");
          delay(1000);
        return p;
      }
        delay(1000); 
      }
        if(mismatch)
        {//this is true if there was a mismatch when scanning same finger; allows enrolment with same id
         lcd.clear();
         lcd.setCursor(0,0);
         lcd.print("Try Enrolling ID");
         lcd.setCursor(0,1);
         lcd.print(id);
         lcd.print(" again-Press #");
         keyPad = keypad.getKey();
         while(!keyPad)
            {
            keyPad = keypad.getKey();
            }
          if(keyPad == '#')
          {
            mismatch = true;
          }
          else
          {
             mismatch  = false;
          }
        }
      }      
    }
    //********************GETTING FINGER ID FROM FLASH*****************
    //Detailed response
    uint8_t getFingerprintID(){
    int p = -1;
           lcd.clear();
           
          if(adminmode)
          {
            lcd.setCursor(0,0);
            lcd.print("Place Admin");
            lcd.setCursor(0,1);
            lcd.print("Finger");
          }
          else
          {
            lcd.setCursor(0,0);
            lcd.print("Place Your");
            lcd.setCursor(0,1);
            lcd.print("Finger");
          }
            delay(1000);
      while (p != FINGERPRINT_OK) {
        p = finger.getImage();
        switch (p) {
        case FINGERPRINT_OK:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Image taken!");
          break;
        case FINGERPRINT_NOFINGER:
          break;
        case FINGERPRINT_PACKETRECIEVEERR:
          lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Communication Error.");
          break;
        case FINGERPRINT_IMAGEFAIL:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Imaging error.");
          break;
        default:
        lcd.clear();
      lcd.setCursor(0,0);
          lcd.print("Unknown error!");
          //Serial.println("Unknown error");
          break;
        }
        delay(1000);
      }
      // OK success!
    delay(1000);
      p = finger.image2Tz();
      switch (p) {
        case FINGERPRINT_OK:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Image converted");
          break;
        case FINGERPRINT_IMAGEMESS:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Image too messy");
          delay(1000);
          //Serial.println("Image too messy");
          return p;
        case FINGERPRINT_PACKETRECIEVEERR:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Communication error");
          delay(1000);
          return p;
        case FINGERPRINT_FEATUREFAIL:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Could not find");
          lcd.setCursor(0,1);
          lcd.print("fingerprint features");
          delay(1000);
          return p;
        case FINGERPRINT_INVALIDIMAGE:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Could not find");
          lcd.setCursor(0,1);
          lcd.print("fingerprint features");
          delay(1000);
          return p;
        default:
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error");
          delay(1000);
          return p;
      }
      // OK converted!
      p = finger.fingerFastSearch();
        if (p == FINGERPRINT_OK) {
         if(!adminmode)
         {    
       
         }
        
      } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Communication error!");
          delay(2000);
        return p;
      } else if (p == FINGERPRINT_NOTFOUND) {
          if(lockmode)
              {
                lcd.clear();
                lcd.setCursor(0,0);
                lcd.print("No Match!");
                digitalWrite(alert, HIGH);
                delay(3000);
                digitalWrite(alert, LOW);     
              }
        return p;
      } else {
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("Unknown error");
          delay(2000);
        return p;
      }   
      if(!adminmode)
         {  
          lcd.clear();
          lcd.setCursor(0,0);
          lcd.print("ENROLLED AS");
          lcd.setCursor(0,1);
          lcd.print("ID => ");
          lcd.print(finger.fingerID);
          delay(2000);
          searched = true;
          if(lockmode)
                 {  
                    digitalWrite(lock, HIGH);
                    delay(2000);
                    digitalWrite(lock, LOW);
                 }
         }
          return finger.fingerID;
    }
