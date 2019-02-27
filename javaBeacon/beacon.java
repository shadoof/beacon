package literalart.imposition;

import java.io.IOException;
import java.io.PrintWriter;
import java.util.Enumeration;
import java.util.Random;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import rita.*;

/**
 * Servlet implementation class for Servlet: Beacon
 *
 */

public class Beacon extends javax.servlet.http.HttpServlet implements
		javax.servlet.Servlet {

	static {
		System.out.println("CP: "+System.getProperty("java.class.path"));
		//System.out.println("\n\n"+System.getProperties());
		Class c = RiTa.class;
	}

	static final long serialVersionUID = 1L;
	static final int BEACON = 0, INSTALLATION = 1, LIGHTHOUSE = 2, CLIENT = 3,
			BROWSER = 4, DRAMA = 5, LIGHTHOUSEPOLL = 6, STOPPED = 9;
	// initially Installation is in control so nothing happens
	// unless Installation starts or a Browser changes things
	// or we are developing/debugging
	static int controlState = BEACON;
	static int prevControlState;
	static final String INITIAL_STATES = "<n>0</n><n>0</n><n>0</n><n>0</n><n>2</n><n>2</n><n>2</n><n>2</n>";
	static final String START_STATES = "<n>0</n><n>0</n><n>0</n><n>0</n><n>0</n><n>0</n><n>0</n><n>0</n>";
	static String theQTList = "<n>" + controlState + "</n>" + INITIAL_STATES;
	// static String rawQTList;
	static String theName = "qtlist";
	static int[] passageLang = { 0, 0, 0, 0 }; // all passages 'German'
	static int[] passageBuoyancy = { 2, 2, 2, 2 }; // all passages 'sinking'
	// previous buoyancies set to sinking+sinking
	static String[] prevBuoyancyKeys = { "<22>", "<22>", "<22>", "<22>" };
	static final String GRAMMAR_FILE = "{  <start>  0 | 1 | 2}{  <00>  [2] 0 | 1 | 2}{  <02>  0 | [2] 2}{  <01>  0 | [2] 1}{  <20>  [2] 0 | [4] 1 | 2}{  <22>  0 | [4] 2}{  <21>  0 | [4] 1}{  <10>  [2] 0 | 1 | [4] 2}{  <12>  0 | [4] 2}{  <11>  0 | [4] 1}{  <000>  [4] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <020>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <010>  [4] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <200>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <220>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <210>  [4] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <100>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <120>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <110>  [8] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <001>  [2] 0 | [4] 1 | [2] 2 | 3 | 4 | 5}{  <021>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <011>  [2] 0 | [4] 1 | [2] 2 | 3 | 4 | 5}{  <201>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <221>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <211>  [2] 0 | [4] 1 | [2] 2 | 3 | 4 | 5}{  <101>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <121>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <111>  [2] 0 | [8] 1 | [2] 2 | 3 | 4 | 5}{  <002>  [2] 0 | [2] 1 | [4] 2 | 3 | 4 | 5}{  <022>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <012>  [2] 0 | [2] 1 | [4] 2 | 3 | 4 | 5}{  <202>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <222>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <212>  [2] 0 | [2] 1 | [4] 2 | 3 | 4 | 5}{  <102>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <122>  [2] 0 | [2] 1 | [2] 2 | 3 | 4 | 5}{  <112>  [2] 0 | [2] 1 | [8] 2 | 3 | 4 | 5}{  <003>  0 | 1 | 2 | [4] 3 | 4 | 5}{  <023>  0 | 1 | 2 | [2] 3 | 4 | 5}{  <013>  0 | 1 | 2 | [4] 3 | 4 | 5}{  <203>  0 | 1 | 2 | [2] 3 | 4 | 5}{  <223>  0 | 1 | 2 | [2] 3 | 4 | 5}{  <213>  0 | 1 | 2 | [4] 3 | 4 | 5}{  <103>  0 | 1 | 2 | [2] 3 | 4 | 5}{  <123>  0 | 1 | 2 | [2] 3 | 4 | 5}{  <113>  0 | 1 | 2 | [4] 3 | 4 | 5}{  <004>  0 | 1 | 2 | 3 | [4] 4 | 5}{  <024>  0 | 1 | 2 | 3 | [2] 4 | 5}{  <014>  0 | 1 | 2 | 3 | [4] 4 | 5}{  <204>  0 | 1 | 2 | 3 | [2] 4 | 5}{  <224>  0 | 1 | 2 | 3 | [2] 4 | 5}{  <214>  0 | 1 | 2 | 3 | [4] 4 | 5}{  <104>  0 | 1 | 2 | 3 | [2] 4 | 5}{  <124>  0 | 1 | 2 | 3 | [2] 4 | 5}{  <114>  0 | 1 | 2 | 3 | [4] 4 | 5}{  <005>  0 | 1 | 2 | 3 | 4 | [4] 5}{  <025>  0 | 1 | 2 | 3 | 4 | [2] 5}{  <015>  0 | 1 | 2 | 3 | 4 | [4] 5}{  <205>  0 | 1 | 2 | 3 | 4 | [2] 5}{  <225>  0 | 1 | 2 | 3 | 4 | [2] 5}{  <215>  0 | 1 | 2 | 3 | 4 | [4] 5}{  <105>  0 | 1 | 2 | 3 | 4 | [2] 5}{  <125>  0 | 1 | 2 | 3 | 4 | [2] 5}{  <115>  0 | 1 | 2 | 3 | 4 | [4] 5}";

	static RiGrammar grammar = new RiGrammar((String)null);

	// if Beacon is in control 10 seconds before the first changes of state
	static final long TFACTOR = 1; // for debugging: set to 1 when done
	static final long MIN_INTERVAL = 20000;
	static long theInterval = MIN_INTERVAL / TFACTOR;
	static final long[] dramaIntervals = { 120000, 150000, 180000 };
	static Random beaconRandom = new Random();
	static long dramaInterval = dramaIntervals[beaconRandom.nextInt(3)]
			/ TFACTOR;
	static long timeOfLastChange = System.currentTimeMillis();
	static long timeOfLastDrama = System.currentTimeMillis();
	static Pattern wspc = Pattern.compile("\\s");
	static Pattern qstart = Pattern.compile("<qtlist>");
	static Pattern qend = Pattern.compile("</qtlist>");
	static Pattern ntag = Pattern.compile("<n>");
	static final String address = "/index.jsp";
	static RequestDispatcher despatcher;
	static int installationPolls = 0;
	static long timeOfBeaconStart;

	/*
	 * (non-Java-doc)
	 *
	 * @see javax.servlet.http.HttpServlet#HttpServlet()
	 */
	public Beacon() {
		super();

		grammar.setGrammarFromString(GRAMMAR_FILE);
	}

	/*
	 * (non-Java-doc)
	 *
	 * @see javax.servlet.http.HttpServlet#doGet(HttpServletRequest request,
	 *      HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request,
			HttpServletResponse response) throws ServletException, IOException {
		/*
		 * unpack whatever has been sent from any polling installation and
		 * change control if necessary
		 */
		Enumeration e = request.getParameterNames();
		if (!e.hasMoreElements()) {
			// this is a browser poll so just send back the previous state
			assembleStateString();
		} else {
			for (e = request.getParameterNames(); e.hasMoreElements();) {
				// theName will = "qtlist"
				theName = (String) e.nextElement();
				// which maps to the string of <n>-tagged elements
				// enclosed by "<qtlist>" tags
				theQTList = request.getParameter(theName);
				// theQTList = rawQTList; // copy the raw list for debugging
				// remove white space from the request list
				theQTList = wspc.matcher(theQTList).replaceAll("");
				// remove qtlist tags
				theQTList = qstart.matcher(theQTList).replaceAll("");
				theQTList = qend.matcher(theQTList).replaceAll("");
				// extract the first element = controller flag
				Matcher m = ntag.matcher(theQTList);
				if (m.find()) {
					int requestControl = Integer.parseInt(theQTList.substring(m
							.end(), m.end() + 1));
					// we have recv'd a string list and we know what's
					// controlling
					// if it's a client request, control & state is unchanged
					if (requestControl == CLIENT) {
						assembleStateString(); // just send back current state
						// leaving controlState as it was
					} else {
						// otherwise change to requested controller
						prevControlState = controlState;
						controlState = requestControl;
						if (controlState == 0)
							installationPolls += 1;
						// if Installation is controlling or
						// this is a lighthouse request then
						// extract states from the list received so we have them
						if ((controlState == INSTALLATION)
								|| (controlState == LIGHTHOUSE)) {
							for (int i = 0; i < passageLang.length; i++)
								if (m.find())
									passageLang[i] = Integer.parseInt(theQTList
											.substring(m.end(), m.end() + 1));
							for (int i = 0; i < passageBuoyancy.length; i++)
								if (m.find())
									passageBuoyancy[i] = Integer
											.parseInt(theQTList.substring(m
													.end(), m.end() + 1));
						}
					} // end client request check
				} // end of block for first "<n>"check
			}
		}

		switch (controlState) {
		case BEACON:
			checkTimeWithProcesses();
			assembleStateString(); // assembles the new string to return
			sendResponse(response);
			break;
		case DRAMA:
			// System.out.println("Drama: ");
			checkDramaEnd();
			assembleStateString(); // assembles the new string to return
			sendResponse(response);
			break;
		case INSTALLATION:
			// just leave theQTList and send it back
			// cause it's controlling
			setIntervalResetTime();
			sendResponse(response);
			break;
		case LIGHTHOUSE:
			controlState = prevControlState; // reset state
			assembleStateString();
			if (theQTList.substring(8).equals(INITIAL_STATES)) {
				controlState = 0;
				assembleStateString();
				theInterval = 30 * 60 * 1000; // will sink for at least thirty
				// mins
			} else if (theQTList.substring(8).equals(START_STATES)) {
				controlState = 0;
				assembleStateString();
				setIntervalResetTime();
				resetDramaInterval();
				timeOfBeaconStart = timeOfLastDrama;
				installationPolls = 0;
				// System.out.println("Lighthouse restart: " + theQTList);
			} else {
				controlState = 0;
				assembleStateString();
				setIntervalResetTime();
				resetDramaInterval();
			}
			forwardRequest(request, response);
			break;
		case LIGHTHOUSEPOLL:
			controlState = prevControlState; // reset state
			if (controlState == 5)
				checkDramaEnd();
			else if (controlState != 1) // if inst. is in control, no change
				checkTimeWithProcesses();
			assembleStateString(); // assembles the new string to return
			// System.out.println("Lighthouse poll: " + theQTList);
			forwardRequest(request, response);
			break;
		}
	}

	private void forwardRequest(HttpServletRequest request,
			HttpServletResponse response) throws ServletException, IOException {
		// forward the request to lighthouse.jsp
		request.setAttribute("controlState", controlState);
		request.setAttribute("theInterval", theInterval / 1000);
		request.setAttribute("intervalTimeLeft", (theInterval - (System
				.currentTimeMillis() - timeOfLastChange)) / 1000);
		request.setAttribute("dramaInterval", dramaInterval / 1000);
		request.setAttribute("dramaTimeLeft", (dramaInterval - (System
				.currentTimeMillis() - timeOfLastDrama)) / 1000);
		request.setAttribute("newList", theQTList);
		request.setAttribute("installationPolls", installationPolls);
		request.setAttribute("sinceBeaconStart",
				(System.currentTimeMillis() - timeOfBeaconStart) / 1000);
		despatcher = request.getRequestDispatcher(address);
		despatcher.forward(request, response);
	}

	private void checkDramaEnd() {
		if (isPastInterval()) {
			controlState = BEACON;
			resetDramaInterval();
		}
	}

	private void resetDramaInterval() {
		dramaInterval = dramaIntervals[beaconRandom.nextInt(3)] / TFACTOR;
		timeOfLastDrama = System.currentTimeMillis();
	}

	private void checkTimeWithProcesses() {
		if (isPastInterval()) {
			if (isPastDrama()) {
				doDrama();
				resetDramaInterval();
			} else {
				// no drama:
				processStates(); // changes states in the array
			}
			setIntervalResetTime();
		}
	}

	private boolean isPastInterval() {
		return (System.currentTimeMillis() - timeOfLastChange) > theInterval;
	}

	private boolean isPastDrama() {
		return (System.currentTimeMillis() - timeOfLastDrama) > dramaInterval;
	}

	private void doDrama() {
		controlState = DRAMA;
		// change all Lang elements to the same value
		// but only - notInLang() if that value is in one of the elements
		int r = 7;
		while (notInLang(r)) r = beaconRandom.nextInt(6);
		for (int i = 0; i < passageLang.length; i++)
			passageLang[i] = r;
		// change all Buoyancy elements to the same value
		// but only - notInBuoyancy() if that value is in one of the elements
		r = 4;
		while (notInBuoyancy(r)) r = beaconRandom.nextInt(3);
		for (int i = 0; i < passageBuoyancy.length; i++)
			passageBuoyancy[i] = r;
	}

	private boolean notInBuoyancy(int r) {
		boolean result = true;
		for (int i = 0; i < passageBuoyancy.length; i++)
			if (passageBuoyancy[i] == r) result = false;
		return result;
	}

	private boolean notInLang(int r) {
		boolean result = true;
		for (int i = 0; i < passageLang.length; i++)
			if (passageLang[i] == r) result = false;
		return result;
	}

	private void sendResponse(HttpServletResponse response) throws IOException {
		// set up the type of response and assign it to out
		response.setContentType("text/html");
		PrintWriter out = response.getWriter();
		// the response is now sent back to the installation or
		// the clients (which can also use Imposition/PassiveBeacon
		if (theQTList.equals(""))
			out.println("theQTList is the empty string");
		else {
			// theQTList = theQTList.substring(40);
			out.println(theQTList);
			// out.println(controlState);
			// for (int i = 0; i < 4; i++)
			// out.println(passageLang[i]);
		}
		out.close();
	}

	private void processStates() {
		// loop through the passages
		for (int i = 0; i < passageLang.length; i++) {
			// assemble a 'language change' key from the previous
			// buoyancy key + current language of each passage
			String langKey = "<" + prevBuoyancyKeys[i].substring(1, 3)
					+ passageLang[i] + ">";
			// collect the new language state from the grammar
			passageLang[i] = Integer.parseInt(grammar.expandFrom(langKey));
		}
		for (int i = 0; i < passageBuoyancy.length; i++) {
			// take the second part of the previous key
			// and add a provisional future state to make a key ...
			String buoyancyKey = "<" + prevBuoyancyKeys[i].substring(2, 3)
					+ grammar.expandFrom(prevBuoyancyKeys[i]) + ">";
			prevBuoyancyKeys[i] = buoyancyKey;
			// that generates the state to be set
			passageBuoyancy[i] = Integer.parseInt(grammar.expandFrom(buoyancyKey));
		}
	}

	private void assembleStateString() {
		theQTList = "<n>" + controlState + "</n>";
		for (int i = 0; i < passageLang.length; i++) {
			theQTList += "<n>" + passageLang[i] + "</n>";
		}
		for (int i = 0; i < passageBuoyancy.length; i++) {
			theQTList += "<n>" + passageBuoyancy[i] + "</n>";
		}
	}

	private void setIntervalResetTime() {
		theInterval = MIN_INTERVAL; // start with a 20 second interval
		for (int i = 0; i < passageBuoyancy.length; i++) {
			// add 5000 for every passage that is surfacing
			if (passageBuoyancy[i] == 1)
				theInterval += 5000;
			// add 2500 for every passage that is sinking
			if (passageBuoyancy[i] == 2)
				theInterval += 2500;
		}
		if (controlState == DRAMA)
			theInterval = theInterval * 2;
		theInterval = theInterval / TFACTOR;
		timeOfLastChange = System.currentTimeMillis();
	}

	/*
	 * (non-Java-doc)
	 *
	 * @see javax.servlet.http.HttpServlet#doPost(HttpServletRequest request,
	 *      HttpServletResponse response)
	 */
	protected void doPost(HttpServletRequest request,
			HttpServletResponse response) throws ServletException, IOException {
		response.setContentType("text/html");
		// PrintWriter out = response.getWriter();
		if (request.getParameter("controlState").equals("0")) {
			controlState = BEACON;
			assembleStateString();
		}

	}

	public static void main(String[] args) {
		System.out.println(theQTList.substring(0, 1)+" "+theQTList.substring(1, 5)+" "+theQTList.substring(5, 9));
	}
}
