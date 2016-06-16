#!/usr/bin/env python
# -*- coding:utf-8 -*-

"""
WALB ( Wireless Attack Launch Box ) User Interface
"""

__author__ = "shiracamus <shiracamus@gmail.com>"
__version__ = "1.0.0"
__date__    = "13 June 2016"

import os
import sys
import time
import threading
import subprocess as Command 
import traceback
import wiringpi
from Queue import Queue, Empty
from ConfigParser import ConfigParser


CONFIG_FILE = "/home/pi/replay.ini"


###############################################################################
# Hardware Input / Output
###############################################################################


class GPIO:     # Raspberry Pi 2 GPIO
    """
    The GPIO accesses the general purpose I/O ports.
    The GPIO is only one in the system, so it has only class members.
    """

    @classmethod
    def setup(self):
        """
        Setup to use the GPIO. 
        """
        wiringpi.wiringPiSetup()                # setup the wiringPi library 
        self.A     = self.DigitalInputPin(0)    # the rotary encorder A
        self.B     = self.DigitalInputPin(1)    # the rotary encorder B
        self.RED   = self.LED(2)                # the LED on the rotary encorder RED
        self.GREEN = self.LED(3)                # the LED on the rotary encorder GREEN
        self.SW    = self.DigitalInputPin(4)    # the push switch on the rotary encorder
        self.A.pullUp()                         # set default level HIGH
        self.B.pullUp()                         # set default level HIGH
        self.SW.pullUp()                        # set default level HIGH


    class DigitalInputPin(object):
        """
        DigitalInputPin can read the pin ether the digital level HIGH or LOW.
        """

        def __init__(self, pin):
            self.pin = pin
            wiringpi.pinMode(pin, wiringpi.INPUT)

        def pullUp(self):
            wiringpi.pullUpDnControl(self.pin, wiringpi.PUD_UP)

        def onBothEdge(self, handler):
            wiringpi.wiringPiISR(self.pin, wiringpi.INT_EDGE_BOTH, handler)

        def read(self):
            return wiringpi.digitalRead(self.pin)


    class DigitalOutputPin(object):
        """
        The DigitalOutputPin can set the pin ether the digital level HIGH or LOW.
        """

        def __init__(self, pin):
            self.pin = pin
            wiringpi.pinMode(pin, wiringpi.OUTPUT)

        def on(self):
            wiringpi.digitalWrite(self.pin, wiringpi.HIGH)

        def off(self):
            wiringpi.digitalWrite(self.pin, wiringpi.LOW)


    class LED(DigitalOutputPin):
        """
        The LED is a DititalOutputPin, it can blink itself.
        """

        def blink(self, count):
            for i in xrange(count):
                self.on()
                Sleep.millis(200)
                self.off()
                Sleep.millis(200)


###############################################################################
# View
###############################################################################


class View:
    """
    The View is a viewer of a model.

    It shows user information of the model.
    """

    @staticmethod
    def display(message1, message2="", message3=""):
        """
        Show messages on the display that able to show 2rows 8columns characters.
        """
        Console.println("{0} {1} {2}".format(message1, message2, message3))
        Command.call(['/bin/i2c-disp.sh', '-i', message1])
        message2 and Command.call(['/bin/i2c-disp.sh', '-p', '0x40', message2])
        message3 and Command.call(['/bin/i2c-disp.sh', '-p', '0x43', message3])


    class MenuDisplay(threading.Thread):
        """
        The MenuDisplay displays the contents of the menu that the user has operated.
        """

        def __init__(self, menu):
            super(View.MenuDisplay, self).__init__()
            self.menu = menu
            self.running = threading.Event()

        def run(self):
            self.running.set()
            while self.running.is_set() and self.menu.waitChanged():
                self.menu.clearChanged()
                View.display(self.menu.title, self.menu.detail)
            self.running.clear()

        def stop(self):
            if self.running.is_set():
                self.running.clear()
                self.menu.setChanged()
                self.join()


    class BusyLED(threading.Thread):
        """
        The BusyLED shows the system is busy to blink an LED.
        You can set 'isBusy' function that returns if the system is busy.
        """

        def __init__(self, led):
            super(View.BusyLED, self).__init__()
            self.led = led
            self.running = threading.Event()
            # event notifier
            self.isBusy = lambda: False

        def run(self):
            self.running.set()
            while self.running.set():
                if self.isBusy():
                    self.led.on()
                    Sleep.millis(500)
                    self.led.off()
                    Sleep.millis(500)
                else:
                    Sleep.millis(1000)

        def stop(self):
            if self.running.is_set():
                self.running.clear()
                self.join()


###############################################################################
# Controller
###############################################################################


class Controller:
    """
    The Controller handles user operations and modify the model.
    """

    class PushSwitch(threading.Thread):
        """
        A push switch device.
        You can set callback functions 'onPushShort', 'onPushLong', 'onReleaseShort' and 'onReleaseLong'.
        """

        PUSHED_LONG_TIME = 2000  # The time to detect pushed long in milli seconds

        def __init__(self, pin):
            super(Controller.PushSwitch, self).__init__()
            self.pin = pin
            self.is_pushed_long = False
            self.running = threading.Event()
            # event handlers
            self.onPushShort = lambda: None
            self.onPushLong = lambda: None
            self.onReleasShort = lambda: None
            self.onReleasLong = lambda: None

        def run(self):
            self.running.set()
            while self.running.is_set():
                if self.isPushed():
                    self.pushed()
                    self.avoidChattering()
                    self.waitToRelease()
                    self.avoidChattering()
                    self.released()
                Sleep.millis(100)

        def stop(self):
            self.running.clear()
            self.join()

        def isPushed(self):
            return self.pin.read() == wiringpi.LOW

        def avoidChattering(self):
            Sleep.millis(50)

        def waitToRelease(self):
            self.is_pushed_long = False
            pushing_time = 0
            while self.isPushed():
                Sleep.millis(50)
                pushing_time += 50
                if not self.is_pushed_long and pushing_time >= self.PUSHED_LONG_TIME:
                    self.is_pushed_long = True
                    self.pushedLong()

        def pushed(self):
            Console.println("pushing...")
            self.onPushShort()

        def pushedLong(self):
            Console.println("pushing long...")
            self.onPushLong()

        def released(self):
            if self.is_pushed_long:
                Console.println("pushed long")
                self.onReleasLong()
            else:
                Console.println("pushed short")
                self.onReleasShort()


    class RotarySensor(object):
        """
        The RotarySensor is a sensor placed inside of a RotaryEncoder.
        It callback specified function when the rotary encoder is rotated.
        """

        def __init__(self, pin, callback, direction):
            self.pin = pin
            self.callback = callback
            self.direction = direction
            self.last_status = None
            pin.onBothEdge(lambda: self.interrupted())

        def interrupted(self):
            current_status = self.pin.read()
            status_changed = self.last_status == wiringpi.LOW and current_status == wiringpi.HIGH
            self.last_status = current_status
            if status_changed:
                self.callback(self.direction)
            else:
                self.callback(0)


    class RotaryEncoder(object):
        """
        The RotaryEncoder is a device, such as a dial, you can enter the rotation direction and rotation steps.
        It has 2 sensors to detect rotation direction (clockwise or counterclockwise) and rotation steps.
        You can set callback functions 'onRotateClockwise' and 'onRotateCounterclockwise'.
        """

        def __init__(self, pinA, pinB):
            self.A = Controller.RotarySensor(pinA, self.rotate, +1)
            self.B = Controller.RotarySensor(pinB, self.rotate, -1)
            self.direction = None
            # event handler
            self.onRotateClockwise = lambda: None
            self.onRotateCounterclockwise = lambda: None

        def rotate(self, direction):
            """
            When a sensor detects rotation, this method is callbacked.
            The direction argument means: +1 is sensed by sensor A, -1 is sensed by sensor B, 0 is the end to rotate.
            
            A +1: ______/~~~~~\__________/~~~~~\______
               0:
            B -1: ~~~\_____/~~~~~~~~~~~~~~~~\_____/~~~
                       clockwise       counterclockwise
            When callbacked by A(+1) after B(-1), the rotation is clockwise.
            """
            if direction == 0 or self.direction == 0:   # unknown rotate direction
                pass
            elif direction == self.direction:           # maybe it is chattering
                pass
            elif direction > self.direction:            # A detected after B
                Console.println("clockwise")
                self.onRotateClockwise()
            elif direction < self.direction:            # B detected after A
                Console.println("counterclockwise")
                self.onRotateCounterclockwise()
            self.direction = direction                  # keep last direction

        def start(self):
            pass

        def stop(self):
            pass


###############################################################################
# Model
###############################################################################


class Model:
    """
    The model is data for controllers and views.
    """

    class MenuBuilder(object):
        """
        The MenuBuilder builds the menu to read a configuration file.
        """

        def __init__(self, config):
            self.config = config

        def buildMainMenu(self):
            menu = self.build('menu', 'select')
            variables = dict(self.config.items('variable'))
            return Model.MainMenu(menu, variables)

        def build(self, section, option):
            target = self.config.get(section, option)
            return eval('self.build_' + target)(section)

        def build_item(self, section):
            items = tuple(self.build(item, 'action')
                          for item in self.config.get(section, 'item').split())
            return Model.Menu(items)

        def build_submenu(self, section):
            get = self.config.get
            title   = get(section, 'title')
            detail  = get(section, 'detail')
            submenu = get(section, 'submenu')
            item    = self.build(submenu, 'select')
            return Model.Menu.SubMenu(title, detail, item)

        def build_value(self, section):
            get = self.config.get
            title    = get(section, 'title')
            variable = get(section, 'variable')
            values   = get(section, 'value').split()
            return Model.Menu.Value(title, variable, values)

        def build_command(self, section):
            get = self.config.get
            title   = get(section, 'title')
            detail  = get(section, 'detail')
            command = get(section, 'command')
            return Model.Menu.Command(title, detail, command)


    class Menu(object):
        """
        The Menu has items and able to select an item and fire its action.
        """

        def __init__(self, items):
            self.items = items
            self.select = 0
            self.submenu = None

        def __repr__(self):
            return 'Menu(%s)' % repr(self.items)

        @property
        def title(self):
            return self.item.title

        @property
        def detail(self):
            return self.item.detail

        @property
        def item(self):
            if self.submenu:
                return self.submenu.item
            else:
                return self.items[self.select]

        def forward(self):
            if self.submenu:
                self.submenu.forward()
            else:
                self.select = (self.select + 1) % len(self.items)

        def backward(self):
            if self.submenu:
                self.submenu.backward()
            else:
                self.select = (self.select - 1) % len(self.items)

        def action(self, variables):
            self.submenu, changed = self.item.action(variables)
            return changed


        class SubMenu(object):
            """
            The SubMenu has an action and able to fire it.
            """

            def __init__(self, title, detail, menu):
                self.title = title
                self.detail = detail
                self.menu = menu

            def __repr__(self):
                return 'SubMenu(%s, %s, %s)' % (repr(self.title), repr(self.detail), repr(self.menu))

            def action(self, variables):
                return self.menu, True      # go down into the sub menu, and update display


        class Value(object):
            """
            The Value has values and able to select a value and set a variable it.
            """

            def __init__(self, title, variable, items):
                self.title = title
                self.variable = variable
                self.items = items
                self.select = 0

            def __repr__(self):
                return 'Value(%s, %s, %s)' % (repr(self.title), repr(self.variable), repr(self.items))

            @property
            def detail(self):
                return self.items[self.select]

            @property
            def item(self):
                return self

            def forward(self):
                self.select = (self.select + 1) % len(self.items)

            def backward(self):
                self.select = (self.select - 1) % len(self.items)

            def action(self, variables):
                variables[self.variable] = self.items[self.select]
                return None, True       # back to the main menu, update display


        class Command(object):
            """
            The Command has a command and able to execute it.
            """

            def __init__(self, title, detail, command):
                self.title = title
                self.detail = detail
                self.command = command

            def __repr__(self):
                return 'Command(%s, %s, %s)' % (repr(self.title), repr(self.detail), repr(self.command))

            def action(self, variables):
                Command.call(self.command.format(**variables), shell=True)
                return None, False      # back to the main menu, no update dispaly for staying the command output


    class MainMenu(object):
        """
        The MainMenu has a menu and able to notice changes its contents to display.
        """

        def __init__(self, menu, variables):
            self.menu = menu
            self.variables = variables
            self.changed = threading.Event()
            self.setChanged()

        def __repr__(self):
            return 'MainMenu(%s, %s)' % (repr(self.menu), repr(self.variables))

        @property
        def title(self):
            return self.menu.title

        @property
        def detail(self):
            return self.menu.detail.format(**self.variables)

        def forward(self):
            self.menu.forward()
            self.setChanged()

        def backward(self):
            self.menu.backward()
            self.setChanged()

        def action(self):
            if self.menu.action(self.variables):
                self.setChanged()

        def setChanged(self):
            self.changed.set()

        def clearChanged(self):
            self.changed.clear()

        def isChanged(self):
            return self.changed.is_set()

        def waitChanged(self):
            return self.changed.wait()


###############################################################################
# System / Library
###############################################################################


class Console(object):
    """
    The Console shows the messages on a console or terminal.
    """

    @classmethod
    def println(self, message):
        if isinstance(message, (tuple, list)):
            print ' '.join(map(str, message))
        else:
            print str(message)


class Sleep:
    """
    Sleep a while.
    """

    @staticmethod
    def days(d):
        Sleep.hours(d * 24)

    @staticmethod
    def hours(h):
        Sleep.minutes(h * 60)

    @staticmethod
    def minutes(m):
        Sleep.seconds(m * 60)

    @staticmethod
    def seconds(s):
        Sleep.millis(s * 1000)

    @staticmethod
    def millis(ms):
        wiringpi.delay(ms)


###############################################################################
# main
###############################################################################


def main():

    # setup I/O
    GPIO.setup()

    # show startup message
    if True:
        Console.println("Config file: " + CONFIG_FILE)
        View.display('replay4', 'python')
        GPIO.RED.blink(2)
        GPIO.GREEN.blink(2)

    # read config
    config = ConfigParser()
    config.read(CONFIG_FILE)

    # build model
    menu = Model.MenuBuilder(config).buildMainMenu()

    # build view
    display = View.MenuDisplay(menu)

    transmit_indicator = View.BusyLED(GPIO.RED)
    transmit_indicator.isBusy = lambda: Command.call(["/home/pi/stat.sh"]) == 1   # transmitting now

    # build controller
    rotary_encoder = Controller.RotaryEncoder(GPIO.A, GPIO.B)
    rotary_encoder.onRotateClockwise = menu.forward
    rotary_encoder.onRotateCounterclockwise = menu.backward

    push_switch = Controller.PushSwitch(GPIO.SW)
    push_switch.onPushShort   = lambda: (GPIO.GREEN.on())
    push_switch.onReleasShort = lambda: (GPIO.GREEN.off(), menu.action())
    push_switch.onPushLong    = lambda: (GPIO.GREEN.off(), GPIO.RED.on())
    push_switch.onReleasLong  = lambda: (GPIO.RED.off(), Command.call(config.get('cancel', 'command'), shell=True))

    # start
    display.start()
    transmit_indicator.start()
    rotary_encoder.start()
    push_switch.start()

    try:
        # main loop
        while True:
            Sleep.seconds(1)
    finally:
        # stop
        push_switch.stop()
        rotary_encoder.stop()
        transmit_indicator.stop()
        display.stop()


if __name__ == '__main__':
    main()
